<?php namespace Celestriode\TargetSelectorConstructure\Context\Audits;

use Celestriode\Captain\Exceptions\CommandSyntaxException;
use Celestriode\Captain\StringReader;
use Celestriode\Constructure\AbstractConstructure;
use Celestriode\Mattock\Parsers\Java\Utils\MinMaxBounds;
use Celestriode\TargetSelectorConstructure\Structures\DynamicOptions\Values\AbstractValue;
use Celestriode\TargetSelectorConstructure\Structures\DynamicOptions\Values\StringValue;

/**
 * Determines whether the input is a valid numeric range. Valid ranges include:
 *
 * 1
 * ..3
 * 5..
 * 7..9
 *
 * @package Celestriode\TargetSelectorConstructure\Context\Audits
 */
class NumericRange extends Numeric
{
    public const INCOMPATIBLE = '5e38ba57-cd08-4a57-8bb2-f513a268428f';
    public const INVALID_SYNTAX = '2865ba46-283e-4ea5-b283-782c76aff974';
    public const OUT_OF_RANGE = '84ca8c1e-6eee-4ab7-bf9e-b467b49c8737';

    /**
     * @inheritDoc
     */
    protected function auditValue(AbstractConstructure $constructure, AbstractValue $input, AbstractValue $expected): bool
    {
        // Skip if the input is not a string parameter.

        if (!($input instanceof StringValue)) {

            $constructure->getEventHandler()->trigger(self::INCOMPATIBLE, $this, $input, $expected);

            return false;
        }

        $num = $input->getStringValue();

        try {

            // Use the parser from MinMaxBounds to create the relevant boundary for comparison.

            $range = MinMaxBounds::fromReader(new StringReader($num));

            if (!$this->validateBounds((float)$range->getMin()) || !$this->validateBounds((float)$range->getMax())) {

                $constructure->getEventHandler()->trigger(self::OUT_OF_RANGE, $this, $input, $expected, $range);

                return false;
            }

        } catch (CommandSyntaxException $e) {

            $constructure->getEventHandler()->trigger(self::INVALID_SYNTAX, $this, $input, $expected, $e);

            return false;
        }

        // Nothing went wrong, audit passes.

        return true;
    }

    /**
     * @inheritDoc
     */
    public static function getName(): string
    {
        return 'numeric_range';
    }
}