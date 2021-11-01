<?php namespace Celestriode\TargetSelectorConstructure\Context\Audits;

use Celestriode\Constructure\AbstractConstructure;
use Celestriode\TargetSelectorConstructure\Structures\DynamicOptions\Values\AbstractValue;

/**
 * Checks to make sure the value type of the input match the expected value type.
 *
 * @package Celestriode\TargetSelectorConstructure\Context\Audits
 */
class TypesMatch extends AbstractValueAudit
{
    public const INCOMPATIBLE = 'a8cdcbc4-e721-47ed-a0ae-881f771b6bf4';

    /**
     * @inheritDoc
     */
    public function auditValue(AbstractConstructure $constructure, AbstractValue $input, AbstractValue $expected): bool
    {
        if (!$expected->compatible($input)) {

            $constructure->getEventHandler()->trigger(self::INCOMPATIBLE, $this, $input, $expected);

            return false;
        }

        return true;
    }

    /**
     * @inheritDoc
     */
    public static function getName(): string
    {
        return 'types_match';
    }
}