<?php namespace Celestriode\TargetSelectorConstructure\Context\Audits;

use Celestriode\Constructure\AbstractConstructure;
use Celestriode\TargetSelectorConstructure\Structures\DynamicOptions\Values\AbstractValue;
use Celestriode\TargetSelectorConstructure\Structures\DynamicOptions\Values\StringValue;

/**
 * Ensures that the parameter is a string and that its value is either "true" or "false" exactly.
 *
 * @package Celestriode\TargetSelectorConstructure\Context\Audits
 */
class Boolean extends AbstractValueAudit
{
    public const INCOMPATIBLE = 'bea50a56-6eae-459d-a9f2-ddccb3f32d44';
    public const INVALID_VALUE = 'c85115c6-6e45-4de3-86d2-7bf24abf57e8';

    public const TRUE = 'true';
    public const FALSE = 'false';

    /**
     * @inheritDoc
     */
    public function auditValue(AbstractConstructure $constructure, AbstractValue $input, AbstractValue $expected): bool
    {
        if (!($input instanceof StringValue)) {

            $constructure->getEventHandler()->trigger(self::INCOMPATIBLE, $this, $input, $expected);

            return false;
        }

        $value = $input->getStringValue();

        // Check if the value is correct.

        if ($value == static::TRUE || $value == static::FALSE) {

            return true;
        }

        $constructure->getEventHandler()->trigger(self::INVALID_VALUE, $this, $input, $expected);

        return false;
    }

    /**
     * @inheritDoc
     */
    public static function getName(): string
    {
        return 'boolean';
    }
}