<?php namespace Celestriode\TargetSelectorConstructure\Context\Audits;

use Celestriode\Constructure\AbstractConstructure;
use Celestriode\Constructure\Context\Audits\AbstractAudit;
use Celestriode\Constructure\Structures\StructureInterface;
use Celestriode\TargetSelectorConstructure\Structures\DynamicOptions\Values\AbstractValue;

/**
 * A simple audit that checks if the incoming structures are parameter values. Useful as a predicate for global audits.
 *
 * @package Celestriode\TargetSelectorConstructure\Context\Audits
 */
class StructureIsValue extends AbstractAudit
{
    public const INCOMPATIBLE = 'aa23ca9c-f25c-44d9-a348-0d29483b10a9';

    /**
     * @inheritDoc
     */
    public function audit(AbstractConstructure $constructure, StructureInterface $input, StructureInterface $expected): bool
    {
        if ($input instanceof AbstractValue && $expected instanceof AbstractValue) {

            return true;
        }

        $constructure->getEventHandler()->trigger(self::INCOMPATIBLE, $this, $input, $expected);

        return false;
    }

    /**
     * @inheritDoc
     */
    public static function getName(): string
    {
        return 'structure_is_value';
    }
}