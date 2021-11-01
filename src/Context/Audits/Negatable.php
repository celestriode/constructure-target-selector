<?php namespace Celestriode\TargetSelectorConstructure\Context\Audits;

use Celestriode\Constructure\AbstractConstructure;
use Celestriode\TargetSelectorConstructure\Structures\DynamicOptions\Values\AbstractValue;

/**
 * Fails if the parameter is negated but is not allowed to be negated. This has a primary use as a global audit.
 *
 * @package Celestriode\TargetSelectorConstructure\Context\Audits
 */
class Negatable extends AbstractValueAudit
{
    public const CANNOT_NEGATE = '44185261-8fc3-4f04-a6ea-192460e611a8';

    /**
     * @inheritDoc
     */
    protected function auditValue(AbstractConstructure $constructure, AbstractValue $input, AbstractValue $expected): bool
    {
        // If the value is negated but the parameter is not negatable, then the audit fails.

        if ($input->isNegated() && !$expected->isNegatable()) {

            $constructure->getEventHandler()->trigger(self::CANNOT_NEGATE, $this, $input, $expected);

            return false;
        }

        return true;
    }

    /**
     * @inheritDoc
     */
    public static function getName(): string
    {
        return 'negatable';
    }
}