<?php namespace Celestriode\TargetSelectorConstructure\Parsers;

use Celestriode\Captain\Exceptions\CommandSyntaxException;
use Celestriode\Captain\StringReader;
use Celestriode\DynamicRegistry\Exception\InvalidValue;
use Celestriode\DynamicRegistry\SimpleRegistry;
use Celestriode\Mattock\Exceptions\MattockException;
use Celestriode\Mattock\Parsers\Java\StringifiedNbtParser;
use Celestriode\Mattock\Parsers\Java\Utils\MinecraftUuid;
use Celestriode\TargetSelectorConstructure\Exceptions\ConversionFailure;
use Celestriode\TargetSelectorConstructure\Structures\AbstractTargetSelector;
use Celestriode\TargetSelectorConstructure\Structures\DynamicOptions\Values\AbstractValue;
use Celestriode\TargetSelectorConstructure\Structures\DynamicOptions\Values\NbtValue;
use Celestriode\TargetSelectorConstructure\Structures\DynamicOptions\Parameter;
use Celestriode\TargetSelectorConstructure\Structures\DynamicOptions\ParametersContainer;
use Celestriode\TargetSelectorConstructure\Structures\DynamicOptions\Values\ParametersValue;
use Celestriode\TargetSelectorConstructure\Structures\DynamicOptions\Values\StringValue;
use Celestriode\TargetSelectorConstructure\Structures\DynamicSelector;
use Celestriode\TargetSelectorConstructure\Structures\PlayerSelector;
use Celestriode\TargetSelectorConstructure\Structures\UuidSelector;
use RuntimeException;

/**
 * Transforms a string into the appropriate StructureInterface structure for comparison.
 *
 * @package Celestriode\TargetSelectorConstructure\Parsers
 */
class TargetSelectorParser
{
    public const TARGETER = '@';
    public const DELIMITER_OPEN = '[';
    public const DELIMITER_CLOSE = ']';
    public const NESTED_DELIMITER_OPEN = '{';
    public const NESTED_DELIMITER_CLOSE = '}';
    public const SEPARATOR = ',';
    public const DESIGNATOR = '=';
    public const NEGATOR = '!';

    /**
     * @var array List of functions to run that overrides automated detection of parameter type.
     */
    protected $parameterOverrides = [];

    public $targeter;
    public $delimiterOpen;
    public $delimiterClose;
    public $nestedDelimiterOpen;
    public $nestedDelimiterClose;
    public $separator;
    public $designator;
    public $negator;

    public function __construct()
    {
        $this->targeter = static::TARGETER;
        $this->delimiterOpen = static::DELIMITER_OPEN;
        $this->delimiterClose = static::DELIMITER_CLOSE;
        $this->nestedDelimiterOpen = static::NESTED_DELIMITER_OPEN;
        $this->nestedDelimiterClose = static::NESTED_DELIMITER_CLOSE;
        $this->separator = static::SEPARATOR;
        $this->designator = static::DESIGNATOR;
        $this->negator = static::NEGATOR;
    }

    /**
     * Adds an override function to a specific parameter based on the incoming name. When a parameter with the provided
     * name is encountered, it will obtain its value through the override rather than attempting to automatically
     * determine what type of value it is.
     *
     * This is particularly useful when there are multiple types of parameters that have similar syntax, such as NBT,
     * JSON, and nested parameters.
     *
     * To override at specific depths, use dot syntax in the input name. For example, overriding the root "nbt" parameter
     * would just use "nbt", but to override a parameter named "four" nested within a parameter list named "scores",
     * the input name should be "scores.four".
     *
     * @param string $name The name of the parameter to override.
     * @param callable $override The override method.
     */
    public function addOverride(string $name, callable $override): void
    {
        $this->parameterOverrides[$name] = $override;
    }

    /**
     * Remove a specific override.
     *
     * @param string $name The name of the override to remove.
     */
    public function removeOverride(string $name): void
    {
        if (isset($this->parameterOverrides[$name])) {

            unset($this->parameterOverrides[$name]);
        }
    }

    /**
     * @return array
     */
    public function getParameterOverrides(): array
    {
        return $this->parameterOverrides;
    }

    /**
    /**
     * Takes in a raw target selector string and produces the appropriate constructure structure.
     *
     * Strings can include player names (e.g."steve"), UUIDs (e.g. "1-1-1-1-1"), or dynamic selectors (e.g. "@p[x=5]").
     *
     * @param string $input The raw string to parse.
     * @return AbstractTargetSelector
     * @throws CommandSyntaxException
     * @throws ConversionFailure
     * @throws InvalidValue
     */
    public function parse(string $input): AbstractTargetSelector
    {
        $reader = new StringReader($input);

        // If the string starts with an @, then it's a dynamic selector.

        if ($reader->peek() == static::TARGETER) {

            return static::parseDynamicSelector($this, $reader);
        }

        // If the string can be parsed as a Minecraft UUID, do so.

        if (MinecraftUuid::valid($input)) {

            return new UuidSelector(MinecraftUuid::normalize($input));
        }

        // Otherwise it can only be a player selector.

        return new PlayerSelector($input);
    }

    /**
     * Parses and returns a dynamic selector.
     *
     * @param TargetSelectorParser $parser
     * @param StringReader $reader The string reader for the input.
     * @return DynamicSelector
     * @throws CommandSyntaxException
     * @throws ConversionFailure
     * @throws InvalidValue
     */
    protected static function parseDynamicSelector(self $parser, StringReader $reader): DynamicSelector
    {
        $reader->skip(); // Skip past the @.

        $target = '';
        $char = '';

        // Get the selector type (such as "a", "e", "p", "s", or "r", or anything else that might be supported.

        while ($reader->canRead()) {

            $char = $reader->read();

            // Stop when parameters are found.

            if ($char == $parser->delimiterOpen) {

                break;
            }

            $target = $target . $char;
        }

        // Collect parameters.

        if ($char == $parser->delimiterOpen && !$reader->canRead()) {

            throw new ConversionFailure('Parameters opened but none were specified.');
        }

        $parameters = static::parseParameters($parser, $reader, $parser->delimiterClose);

        // If there was no target, then use an empty registry.

        if (empty($target)) {

            return new DynamicSelector(new SimpleRegistry(), $parameters);
        }

        // Otherwise us a registry with only the target inside it.

        return new DynamicSelector(new SimpleRegistry($target), $parameters);
    }

    /**
     * Parses a set of parameters within either a set of [] or {}. The opening delimiter will have already been
     * skipped prior to calling this method.
     *
     * Returns a specialized collection of parameters.
     *
     * @param TargetSelectorParser $parser
     * @param StringReader $reader The string reader for the input.
     * @param string $closingDelimiter When to stop searching the parameters.
     * @param string $parentLevel
     * @return ParametersContainer
     * @throws CommandSyntaxException
     * @throws ConversionFailure
     */
    protected static function parseParameters(self $parser, StringReader $reader, string $closingDelimiter, string $parentLevel = ''): ParametersContainer
    {
        $parameters = [];

        // Per-parameter.

        while ($reader->canRead() && $reader->peek() != $closingDelimiter) {

            // Get the name of the parameter.

            $name = static::getParameterName($parser, $reader);
            $path = ($parentLevel == '' ? $name : $parentLevel . '.' . $name);

            // Create and append the parameter to the list of parameters.

            if (!isset($parameters[$name])) {

                $parameters[$name] = new Parameter($name);
            }

            $parameter = $parameters[$name];

            // Mark the parameter as being negated if it is.

            $negated = false;

            if ($reader->peek() == $parser->negator) {

                $negated = true;
                $reader->skip();
            }

            // Get the value of the parameter, whether through an override or automatic obtainment.

            $override = $parser->getParameterOverrides()[$path] ?? null;

            if ($override !== null) {
                try {

                    $value = $override($parser, $reader, $parameter);
                } catch(CommandSyntaxException $e) {

                    throw new ConversionFailure($e->getMessage());
                }
            } else {

                $value = static::getParameterValue($parser, $reader, $parameter, $path);

                if (!($value instanceof AbstractValue)) {

                    throw new RuntimeException('Parameter value override must return an AbstractValue.');
                }
            }

            $parameters[$name]->addValue($value->setNegated($negated));

            // Skip separator for next parameter if it exists.

            if ($reader->peek() != $parser->separator && $reader->peek() != $closingDelimiter) {

                throw new ConversionFailure('Unexpected character at position ' . $reader->getCursor() + 1 . ': ' . $reader->peek());
            }

            // Skip trailing comma, should it exist. Closing delimiter handled by the while loop.

            if ($reader->peek() == $parser->separator) {

                $reader->skip();
            }
        }

        // Return the new set of parameters.

        return new ParametersContainer(...array_values($parameters));
    }

    /**
     * Returns the name of the parameter, stopping at the first equals sign.
     *
     * @param TargetSelectorParser $parser
     * @param StringReader $reader The string reader for the input.
     * @return string
     */
    protected static function getParameterName(self $parser, StringReader $reader): string
    {
        $name = '';

        while ($reader->canRead()) {

            $char = $reader->read();

            if ($char == $parser->designator) {

                break;
            }

            $name = $name . $char;
        }

        return $name;
    }

    /**
     * Automatically obtains the value of the parameter. This will not occur if the parameter has an override function.
     *
     * Possible value types include a basic string, NBT data, and a nested set of parameters.
     *
     * @param TargetSelectorParser $parser
     * @param StringReader $reader The string reader for the input.
     * @param Parameter $parameter
     * @param string $parentLevel
     * @return AbstractValue
     * @throws CommandSyntaxException
     * @throws ConversionFailure
     */
    protected static function getParameterValue(self $parser, StringReader $reader, Parameter $parameter, string $parentLevel): AbstractValue
    {
        if (!$reader->canRead()) {

            throw new ConversionFailure('Prematurely reached the end of parameters.');
        }

        // Try a variety of parser types.

        if ($reader->peek() == $parser->nestedDelimiterOpen) {

            // Attempt nested parameter parser.

            $start = $reader->getCursor();

            try {

                return static::parseValueAsNested($parser, $reader, $parameter, $parentLevel);
            } catch (ConversionFailure $e) {

                $reader->setCursor($start);
            }

            // Attempt NBT parser.

            try {

                return static::parseValueAsSnbt($parser, $reader, $parameter);
            } catch (CommandSyntaxException|MattockException $e) {

                $reader->setCursor($start);
            }

            // Attempt JSON parser.

            // No parser worked, just attempt to get to the next instance of }.

            return new StringValue($parameter, $reader->readStringUntil($parser->nestedDelimiterClose));
        }

        // Only possible option left is it being a simple string.

        return new StringValue($parameter, $reader->readString());
    }

    /**
     * Parses the input as a parameter value that contains nested parameters.
     *
     * @param TargetSelectorParser $parser
     * @param StringReader $reader The string reader for the input.
     * @param Parameter $parameter
     * @param string $parentLevel
     * @return ParametersValue
     * @throws CommandSyntaxException
     * @throws ConversionFailure
     */
    public static function parseValueAsNested(self $parser, StringReader $reader, Parameter $parameter, string $parentLevel): ParametersValue
    {
        $reader->skip();
        $parameters = static::parseParameters($parser, $reader, $parser->nestedDelimiterClose, $parentLevel);
        $reader->skip();

        return new ParametersValue($parameter, $parameters);
    }

    /**
     * Parses the input as a parameter value that contains NBT data.
     *
     * @param TargetSelectorParser $parser
     * @param StringReader $reader The string reader for the input.
     * @param Parameter $parameter The parameter to link the value to.
     * @return NbtValue
     * @throws CommandSyntaxException
     * @throws MattockException
     */
    public static function parseValueAsSnbt(self $parser, StringReader $reader, Parameter $parameter): NbtValue
    {
        $nbt = (new StringifiedNbtParser($reader))->parseCompoundTag();

        return new NbtValue($parameter, $nbt);
    }

    /**
     * Parses the input as a parameter value that contains a resource location.
     *
     * @param TargetSelectorParser $parser
     * @param StringReader $reader The string reader for the input.
     * @param Parameter $parameter
     * @param string ...$end
     * @return StringValue
     */
    public static function parseValueUntil(self $parser, StringReader $reader, Parameter $parameter, string ...$end): StringValue
    {
        $buffer = '';

        while ($reader->canRead() && !in_array($reader->peek(), $end)) {

            $buffer = $buffer . $reader->read();
        }

        return new StringValue($parameter, $buffer);
    }

    public static function forceSnbt(): callable
    {
        return function(TargetSelectorParser $parser, StringReader $reader, Parameter $parameter) {

            return $parser::parseValueAsSnbt($parser, $reader, $parameter);
        };
    }

    public static function forceValueUntil(string ...$end): callable
    {
        return function(TargetSelectorParser $parser, StringReader $reader, Parameter $parameter) use ($end) {

            return $parser::parseValueUntil($parser, $reader, $parameter, ...$end);
        };
    }
}