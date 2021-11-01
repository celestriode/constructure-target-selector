<?php namespace Celestriode\TargetSelectorConstructure\Structures\DynamicOptions;

use Celestriode\Constructure\AbstractConstructure;
use Celestriode\Constructure\Context\Audits\AbstractAudit;
use Celestriode\Constructure\Context\PrettifierInterface;
use Celestriode\Constructure\Structures\AbstractStructure;
use Celestriode\Constructure\Structures\StructureInterface;
use Celestriode\TargetSelectorConstructure\Exceptions\SelectorException;
use Celestriode\TargetSelectorConstructure\Parsers\TargetSelectorParser;
use Celestriode\TargetSelectorConstructure\Structures\DynamicOptions\Values\AbstractValue;

/**
 * A holding for an array of parameters.
 *
 * @package Celestriode\TargetSelectorConstructure\Structures\DynamicOptions
 */
class ParametersContainer extends AbstractStructure
{
    public const INCOMPATIBLE = 'c9f9758a-6a16-4c49-9a76-67836dfa8dfb';
    public const UNEXPECTED_PARAMETER = 'a56af125-4e61-46f4-b3dc-d8d250da3aab';

    /**
     * @var array The list of parameters as key-value pairs. Use a mixed value for multiple types to a single key.
     */
    protected $parameters = [];

    public function __construct(Parameter ...$parameters)
    {
        $this->addParameters(...$parameters);

        parent::__construct();
    }

    /**
     * Adds multiple parameters to the container.
     *
     * @param Parameter[] $parameters An array of parameters.
     * @return $this
     */
    public function addParameters(Parameter ...$parameters): self
    {
        foreach ($parameters as $value) {

            $this->addParameter($value);
        }

        return $this;
    }

    /**
     * Adds a single parameter to the container.
     *
     * @param Parameter $parameter
     * @return $this
     */
    public function addParameter(Parameter $parameter): self
    {
        $this->setParameter($parameter->getKey(), $parameter);

        return $this;
    }

    /**
     * Adds a value to the parameter container. If the key did not exist within the container, it will add it.
     *
     * @param string|null $key
     * @param AbstractValue $value
     * @return $this
     */
    public function addValue(?string $key, AbstractValue $value): self
    {
        try {
            $parameter = $this->getParameter($key);
        } catch (SelectorException $e) {

            $parameter = new Parameter($key);
            $this->setParameter($key, $parameter);
        } finally {

            $parameter->addValue($value);
        }

        return $this;
    }

    /**
     * Adds audits to the parameter of the given key. Can be used to validate a group of values if more than
     * one value with the same name is specified. The parameter will be created if it doesn't already exist.
     *
     * TODO: does not support nested parameter keys.
     *
     * @param string|null $key
     * @param AbstractAudit ...$audits
     * @return $this
     */
    public function addAuditsToParameter(?string $key, AbstractAudit ...$audits): self
    {
        try {
            $parameter = $this->getParameter($key);
        } catch (SelectorException $e) {

            $parameter = new Parameter($key);
            $this->setParameter($key, $parameter);
        } finally {

            $parameter->addAudits(...$audits);
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function compare(AbstractConstructure $constructure, StructureInterface $other): bool
    {
        if (!($other instanceof ParametersContainer)) {

            $constructure->getEventHandler()->trigger(self::INCOMPATIBLE, $other, $this);

            return false;
        }

        $parametersMatched = true;
        $placeholder = $this->getPlaceholder();
        $placeholderCandidates = [];

        // Check each parameter on the input.

        /**
         * @var Parameter $parameter
         */
        foreach ($other->getParameters() as $key => $parameter) {

            try {

                $expected = $this->getParameter($key);

                if (!$expected->compare($constructure, $parameter)) {

                    $parametersMatched = false;
                }
            } catch (SelectorException $e) {

                // Invalid parameter name. Handle differently if there is a placeholder.

                if ($placeholder === null) {

                    $constructure->getEventHandler()->trigger(self::UNEXPECTED_PARAMETER, $other, $this, $parameter);

                    $parametersMatched = false;
                } else {

                    $placeholderCandidates[] = $parameter;
                }
            }
        }

        // Check placeholders.

        foreach ($placeholderCandidates as $candidate) {

            if (!$placeholder->compare($constructure, $candidate)) {

                $constructure->getEventHandler()->trigger(self::UNEXPECTED_PARAMETER, $other, $this, $candidate);

                $parametersMatched = false;
            }
        }

        // All set, check audits.

        return parent::compare($constructure, $other) && $parametersMatched;
    }

    /**
     * Returns the first parameter in the array that does not have a name. A parameter without a name is a placeholder,
     * which means that any input parameters with custom, user-defined names will be compared with the placeholder's
     * value only. This an be useful if the name of the parameter can be anything while the value is still important.
     *
     * Since only one placeholder is allowed, use a MixedValue parameter to hold more than one value type.
     *
     * @return AbstractValue|null
     */
    protected function getPlaceholder(): ?Parameter
    {
        return $this->getParameters()[''] ?? null;
    }

    /**
     * Returns a single parameter from the list based on the name of the parameter.
     *
     * @param string|null $key The name of the parameter to get.
     * @return Parameter
     * @throws SelectorException
     */
    public function getParameter(?string $key): Parameter
    {
        if (isset($this->getParameters()[$key ?? ''])) {

            return $this->getParameters()[$key ?? ''];
        }

        throw new SelectorException('Parameter ' . ($key ?? 'NULL') . ' not found.');
    }

    /**
     * Adds or overwrites a parameter to the container with the optionally-supplied parameter. If no parameter is given,
     * a new empty parameter is used.
     *
     * @param string|null $key
     * @param Parameter|null $parameter
     * @return $this
     */
    public function setParameter(?string $key, Parameter $parameter = null): self
    {
        $this->parameters[$key] = $parameter ?? new Parameter($key);

        return $this;
    }

    /**
     * Returns all parameters.
     *
     * @return array
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    /**
     * Returns whether or not there are any parameters.
     *
     * @return bool
     */
    public function isEmpty(): bool
    {
        return count($this->getParameters()) == 0;
    }

    /**
     * @inheritDoc
     */
    public function toString(PrettifierInterface $prettifier = null): string
    {
        $buffer = '';
        $num = count($this->getParameters());
        $i = 0;

        /**
         * @var Parameter $parameter
         */
        foreach ($this->getParameters() as $parameter) {

            $buffer = $buffer . $parameter->toString($prettifier);

            if ($i + 1 != $num) {

                $buffer = $buffer . TargetSelectorParser::SEPARATOR;
            }

            $i++;
        }

        return $buffer;
    }
}