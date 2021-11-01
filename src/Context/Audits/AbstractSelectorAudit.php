<?php namespace Celestriode\TargetSelectorConstructure\Context\Audits;

use Celestriode\Constructure\AbstractConstructure;
use Celestriode\Constructure\Context\Audits\AbstractAudit;
use Celestriode\Constructure\Structures\StructureInterface;
use Celestriode\TargetSelectorConstructure\Structures\AbstractTargetSelector;

/**
 * Audits that are specifically used to validate an overarching target selector.
 *
 * These audits can be useful for processing complex logic, such as the "type" parameter not being relevant for "@a" or
 * "@p".
 *
 * @package Celestriode\TargetSelectorConstructure\Context\Audits
 */
abstract class AbstractSelectorAudit extends AbstractAudit
{
    public const INCOMPATIBLE = 'd6bf6155-3329-4d86-8044-0c82e89f0add';

    /**
     * Audit an entire target selector.
     *
     * @param AbstractConstructure $constructure The base Constructure object, which holds the event handler.
     * @param AbstractTargetSelector $input The input to be compared with the expected structure.
     * @param AbstractTargetSelector $expected The expected structure that the input should adhere to.
     * @return bool
     */
    abstract protected function auditSelector(AbstractConstructure $constructure, AbstractTargetSelector $input, AbstractTargetSelector $expected): bool;

    /**
     * @inheritDoc
     */
    public function audit(AbstractConstructure $constructure, StructureInterface $input, StructureInterface $expected): bool
    {
        // Ensure both structures are target selectors.

        if (!($input instanceof AbstractTargetSelector) || !($expected instanceof AbstractTargetSelector)) {

            $constructure->getEventHandler()->trigger(self::INCOMPATIBLE, $this, $input, $expected);

            return false;
        }

        return $this->auditSelector($constructure, $input, $expected);
    }
}