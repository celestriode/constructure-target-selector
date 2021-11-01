<?php namespace Celestriode\TargetSelectorConstructure\Structures\DynamicOptions;

use Celestriode\Constructure\AbstractConstructure;
use Celestriode\Constructure\Context\PrettifierInterface;
use Celestriode\Constructure\Structures\AbstractStructure;
use Celestriode\Constructure\Structures\StructureInterface;
use Celestriode\TargetSelectorConstructure\Parsers\TargetSelectorParser;
use Celestriode\TargetSelectorConstructure\Structures\DynamicOptions\Values\AbstractValue;

/**
 * A single parameter which holds a name and a value.
 *
 * Values can also be negated. Use the Negatable audit to check whether or not negated inputs are acceptable.
 *
 * @package Celestriode\TargetSelectorConstructure\Structures\DynamicOptions
 */
class Parameter extends AbstractStructure
{
    public const INCOMPATIBLE = '0a1fc0a5-9d78-4135-96e3-32c5fef976d7';
    public const ONLY_ONE_ALLOWED = 'c8b53cbb-6799-4dfc-9457-ba1ce2e31aa4';

    /**
     * @var string|null The name of the parameter, which applies to all of its stored values.
     */
    protected $key;

    /**
     * @var AbstractValue[] The name of the parameter. If not present, the parameter is a placeholder.
     */
    protected $values = [];

    public function __construct(?string $key, $input = null)
    {
        $this->key = $key;

        parent::__construct($input);
    }

    /**
     * Returns the name of the parameter.
     *
     * @return string|null
     */
    public function getKey(): ?string
    {
        return $this->key;
    }

    /**
     * Returns the values associated with this parameter.
     *
     * @return AbstractValue[]
     */
    public function getValues(): array
    {
        return $this->values;
    }

    /**
     * Adds multiple values to this multi-value.
     *
     * @param AbstractValue ...$values
     * @return $this
     */
    public function addValues(AbstractValue ...$values): self
    {
        foreach ($values as $value) {

            $this->addValue($value);
        }

        return $this;
    }

    /**
     * Adds a value to this multi-value, provided that it is allowed.
     *
     * @param AbstractValue $value
     * @return Parameter
     */
    public function addValue(AbstractValue $value): self
    {
        $this->values[] = $value;
        $value->setKey($this->getKey());
        $value->setParameter($this);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function compare(AbstractConstructure $constructure, StructureInterface $other): bool
    {
        if (!($other instanceof Parameter)) {

            $constructure->getEventHandler()->trigger(self::INCOMPATIBLE, $other, $this);

            return false;
        }

        $result = true;

        // Cycle through all of the values stored in this parameter and compare them to the input.

        foreach ($this->getValues() as $value) {

            $numOfValue = 0;
            $multipleAllowed = $value->supportsMultiple();

            foreach ($other->getValues() as $otherValue) {

                $numOfValue++;

                if (!$value->compare($constructure, $otherValue)) {

                    $result = false;
                }
            }

            // Check to make sure there can be more than one of the value within this parameter.

            if ($numOfValue > 1 && !$multipleAllowed) {

                $constructure->getEventHandler()->trigger(self::ONLY_ONE_ALLOWED, $other, $this);

                $result = false;
            }
        }

        return parent::compare($constructure, $other) && $result;
    }

    /**
     * @inheritDoc
     */
    public function toString(PrettifierInterface $prettifier = null): string
    {
        $buffer = '';
        $num = count($this->getValues());
        $i = 0;

        /**
         * @var Parameter $parameter
         */
        foreach ($this->getValues() as $value) {

            $buffer = $buffer . $this->getKey() . TargetSelectorParser::DESIGNATOR . ($value->isNegated() ? TargetSelectorParser::NEGATOR : '') . $value->toString($prettifier);

            if ($i + 1 != $num) {

                $buffer = $buffer . TargetSelectorParser::SEPARATOR;
            }

            $i++;
        }

        return $buffer;
    }
}