<?php namespace Celestriode\TargetSelectorConstructure\Context\Audits;

use Celestriode\Constructure\AbstractConstructure;
use Celestriode\Constructure\Context\Audits\AbstractAudit;
use Celestriode\Constructure\Structures\StructureInterface;
use Celestriode\TargetSelectorConstructure\Structures\DynamicOptions\Values\AbstractValue;

/**
 * Audits that are specifically used to validate a single incoming parameter.
 *
 * @package Celestriode\TargetSelectorConstructure\Context\Audits
 */
abstract class AbstractValueAudit extends AbstractAudit
{
    public const INCOMPATIBLE = 'f0ff44ba-3526-4fa4-b7ca-e40db66cd404';

    /**
     * Audit a parameter's value.
     *
     * @param AbstractConstructure $constructure The base Constructure object, which holds the event handler.
     * @param AbstractValue $input The input to be compared with the expected structure.
     * @param AbstractValue $expected The expected structure that the input should adhere to.
     * @return bool
     */
    abstract protected function auditValue(AbstractConstructure $constructure, AbstractValue $input, AbstractValue $expected): bool;

    /**
     * @inheritDoc
     */
    public function audit(AbstractConstructure $constructure, StructureInterface $input, StructureInterface $expected): bool
    {
        // Ensure the both structures are a single parameter.

        if (!($input instanceof AbstractValue) || !($expected instanceof AbstractValue)) {

            $constructure->getEventHandler()->trigger(self::INCOMPATIBLE, $this, $input, $expected);

            return false;
        }

        return $this->auditValue($constructure, $input, $expected);
    }
}