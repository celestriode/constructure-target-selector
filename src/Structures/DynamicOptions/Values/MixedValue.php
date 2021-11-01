<?php namespace Celestriode\TargetSelectorConstructure\Structures\DynamicOptions\Values;

use Celestriode\Constructure\AbstractConstructure;
use Celestriode\Constructure\Context\PrettifierInterface;
use Celestriode\Constructure\Structures\StructureInterface;
use Celestriode\TargetSelectorConstructure\Structures\DynamicOptions\Parameter;

/**
 * A mixed value contains multiple types of values.
 *
 * @package Celestriode\TargetSelectorConstructure\Structures\DynamicOptions
 */
class MixedValue extends AbstractValue
{
    public const INCOMPATIBLE = '87837150-b55d-496a-b2a1-aa3c54de6d77';

    public function __construct(Parameter $parameter, AbstractValue ...$values)
    {
        parent::__construct($parameter, $values);
    }

    /**
     * @inheritDoc
     */
    public function compare(AbstractConstructure $constructure, StructureInterface $other): bool
    {
        if (!($other instanceof AbstractValue)) {

            $constructure->getEventHandler()->trigger(self::INCOMPATIBLE, $other, $this);

            return false;
        }

        // Cycle through the possible inputs and compare the incoming parameter to it.

        $success = false;

        foreach ($this->getMixedValue() as $valueType) {

            // If the possible type matches the incoming type, compare them.

            if (get_class($valueType) == get_class($other)) {

                $success = $valueType->compare($constructure, $other);
                break;
            }
        }

        return parent::compare($constructure, $other) && $success;
    }

    /**
     * A mixed value should not use global audits; rather, its supported values should be doing the auditing.
     *
     * @return bool
     */
    public function useGlobalAudits(): bool
    {
        return false;
    }

    /**
     * Returns the value as an array of parameters.
     *
     * @return AbstractValue[]
     */
    public function getMixedValue(): array
    {
        return $this->getValue();
    }

    /**
     *
     * @inheritDoc
     */
    public function toString(PrettifierInterface $prettifier = null): string
    {
        return 'mixed_value';
    }
}