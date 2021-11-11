<?php namespace Celestriode\TargetSelectorConstructure\Structures;

use Celestriode\Constructure\AbstractConstructure;
use Celestriode\Constructure\Context\PrettifierInterface;
use Celestriode\Constructure\Structures\AbstractStructure;
use Celestriode\Constructure\Structures\StructureInterface;
use Celestriode\DynamicRegistry\AbstractRegistry;
use Celestriode\Mattock\Parsers\Java\Nbt\Tags\CompoundTag;
use Celestriode\TargetSelectorConstructure\Exceptions\SelectorException;
use Celestriode\TargetSelectorConstructure\Structures\DynamicOptions\Parameter;
use Celestriode\TargetSelectorConstructure\Structures\DynamicOptions\ParametersContainer;
use Celestriode\TargetSelectorConstructure\Structures\DynamicOptions\Values\AbstractValue;
use Celestriode\TargetSelectorConstructure\Structures\DynamicOptions\Values\MixedValue;
use Celestriode\TargetSelectorConstructure\Structures\DynamicOptions\Values\NbtValue;
use Celestriode\TargetSelectorConstructure\Structures\DynamicOptions\Values\ParametersValue;
use Celestriode\TargetSelectorConstructure\Structures\DynamicOptions\Values\StringValue;

/**
 * The root of a target selector. When instantiating, you can supply different types of target selectors that the input
 * is allowed to be. When validating, the input will be compared to each one, including any duplicated types. This means
 * you can have multiple definitions of a target selector where only one needs to pass.
 *
 * @package Celestriode\TargetSelectorConstructure\Structures
 */
class Selector extends AbstractStructure
{
    public const INVALID_SELECTOR_TYPE = 'b45516e7-cce9-4991-a113-480e29038e38';

    /**
     * @var AbstractTargetSelector[] The acceptable target selector types.
     */
    protected $accepted = [];

    /**
     * @var Parameter A default parameter placeholder for values added erroneously. This prevents a value from having no parent.
     * This is also a pretty awful hack.
     */
    protected static $parameterPlaceholder;

    public function __construct(AbstractTargetSelector ...$accepted)
    {
        $this->accepted = $accepted;

        parent::__construct();
    }

    /**
     * Creates a placeholder parameter as a hack to allow the various value-creating static methods in this class.
     *
     * @return Parameter
     */
    final protected static function getParameterPlaceholder(): Parameter
    {
        if (self::$parameterPlaceholder === null) {

            return self::$parameterPlaceholder = new Parameter(null);
        }

        return self::$parameterPlaceholder;
    }

    /**
     * Adds a selector to the list of accepted target selector types to the root.
     *
     * @param AbstractTargetSelector $selector
     * @return $this
     */
    public function addAcceptedSelector(AbstractTargetSelector $selector): self
    {
        $this->accepted[] = $selector;

        return $this;
    }

    /**
     * @inheritDoc
     * @throws SelectorException
     */
    public function compare(AbstractConstructure $constructure, StructureInterface $other): bool
    {
        if (!($other instanceof Selector)) {

            throw new SelectorException('Input is not a selector structure.');
        }

        $succeeds = false;

        // Ensure that at least one of the accepted values matches.

        foreach ($this->getAccepted() as $accepted) {

            foreach ($other->getAccepted() as $otherAccepted) {

                if ($accepted->getSelectorType() == $otherAccepted->getSelectorType() && $accepted->compare($constructure, $otherAccepted)) {

                    $succeeds = true;

                    break 2;
                }
            }
        }

        if (!$succeeds) {

            $constructure->getEventHandler()->trigger(self::INVALID_SELECTOR_TYPE, $other, $this);
        }

        return parent::compare($constructure, $other) && $succeeds;
    }

    /**
     * Returns all the accepted target selector types.
     *
     * @return AbstractTargetSelector[]
     */
    public function getAccepted(): array
    {
        return $this->accepted;
    }

    /**
     * @inheritDoc
     */
    public function toString(PrettifierInterface $prettifier = null): string
    {
        if (count($this->getAccepted()) == 1) {

            return $this->getAccepted()[0]->toString($prettifier);
        }

        return 'mixed_selector';
    }

    /**
     * Creates the root node for target selectors.
     *
     * @param AbstractTargetSelector ...$accepted
     * @return static
     */
    public static function root(AbstractTargetSelector ...$accepted): self
    {
        return new static(...$accepted);
    }

    /**
     * Creates a name-based target selector.
     *
     * @return PlayerSelector
     */
    public static function name(): PlayerSelector
    {
        return new PlayerSelector();
    }

    /**
     * Creates a UUID-based target selector.
     *
     * @return UuidSelector
     */
    public static function uuid(): UuidSelector
    {
        return new UuidSelector();
    }

    /**
     * Creates a dynamic target selector with the provided target types and parameters.
     *
     * @param AbstractRegistry $targetTypes The allowable target types (e.g. "p", "e", "a", "r", and "s").
     * @return DynamicSelector
     */
    public static function dynamic(AbstractRegistry $targetTypes): DynamicSelector
    {
        return new DynamicSelector($targetTypes, new ParametersContainer());
    }

    /**
     * Creates and returns a new string value.
     *
     * @param string|null $input
     * @return StringValue
     */
    public static function string(string $input = null): StringValue
    {
        return new StringValue(self::getParameterPlaceholder(), $input);
    }

    /**
     * Creates and returns a new SNBT value.
     *
     * @param CompoundTag|null $nbt
     * @return NbtValue
     */
    public static function snbt(CompoundTag $nbt = null): NbtValue
    {
        return new NbtValue(self::getParameterPlaceholder(), $nbt);
    }

    /**
     * Creates and returns a new mixed value.
     *
     * @param AbstractValue ...$types
     * @return MixedValue
     */
    public static function mixed(AbstractValue ...$types): MixedValue
    {
        return new MixedValue(self::getParameterPlaceholder(), ...$types);
    }

    /**
     * Creates and returns a value that is a new nested set of parameters.
     *
     * @param ParametersContainer|null $parameters
     * @return ParametersValue
     */
    public static function nested(ParametersContainer $parameters = null): ParametersValue
    {
        return new ParametersValue(self::getParameterPlaceholder(), $parameters ?? new ParametersContainer());
    }
}