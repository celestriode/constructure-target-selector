<?php namespace Celestriode\TargetSelectorConstructure\Context\Audits;

use Celestriode\Constructure\AbstractConstructure;
use Celestriode\Mattock\Parsers\Java\Utils\MinMaxBounds;
use Celestriode\TargetSelectorConstructure\Structures\DynamicOptions\Values\AbstractValue;
use Celestriode\TargetSelectorConstructure\Structures\DynamicOptions\Values\StringValue;

/**
 * Determines whether or not the input is a valid numeric value within a specific range. The min/max of the range may be
 * set to null to ignore either or both bounds.
 *
 * @package Celestriode\TargetSelectorConstructure\Context\Audits
 */
class Numeric extends AbstractValueAudit
{
    public const INCOMPATIBLE = 'd459635b-370f-41fc-9cee-db806214afe6';
    public const OUT_OF_RANGE = 'e8a806bd-0f64-431f-b4a7-e24c0b6d86b7';

    /**
     * @var MinMaxBounds The range that the input is allowed to be within.
     */
    protected $bounds;

    /**
     * @var bool Whether or not the allowed range is inclusive (vs exclusive).
     */
    protected $inclusive;

    public function __construct(MinMaxBounds $bounds = null, bool $inclusive = true)
    {
        $this->bounds = $bounds ?? new MinMaxBounds(null, null);
        $this->inclusive = $inclusive;
    }

    /**
     * @inheritDoc
     */
    protected function auditValue(AbstractConstructure $constructure, AbstractValue $input, AbstractValue $expected): bool
    {
        if (!($input instanceof StringValue) || !is_numeric($input->getStringValue())) {

            $constructure->getEventHandler()->trigger(self::INCOMPATIBLE, $this, $input, $expected);

            return false;
        }

        // If the bounds are both null, just return true.

        if (!$this->validateBounds((float)$input->getStringValue())) {

            $constructure->getEventHandler()->trigger(self::OUT_OF_RANGE, $this, $input, $expected);

            return false;
        }

        // Otherwise check those bounds.

        return true;
    }

    /**
     * Returns whether or not the input is within the expected range.
     *
     * @param float $num The value to check.
     * @return bool
     */
    protected function validateBounds(float $num): bool
    {
        return $this->getBounds()->within($num, $this->isInclusive());
    }

    /**
     * Returns the range in which the expected value is allowed to be within. If either min or max are null, then there
     * is no limit on those ends. Both ends can be null, effectively turning the audit into a simple numeric check.
     *
     * @return MinMaxBounds
     */
    public function getBounds(): MinMaxBounds
    {
        return $this->bounds;
    }

    /**
     * Returns whether or not the expected numeric value is inclusive within the allowed range. False means exclusive.
     *
     * @return bool
     */
    public function isInclusive(): bool
    {
        return $this->inclusive;
    }

    /**
     * @inheritDoc
     */
    public static function getName(): string
    {
        return 'numeric';
    }

    /**
     * @inheritDoc
     */
    public function toString(): string
    {
        return parent::toString() . '{min=' . $this->getBounds()->getMin() . ',max=' . $this->getBounds()->getMax() . ',inclusive=' . ($this->isInclusive() ? 'true' : 'false') . '}';
    }
}