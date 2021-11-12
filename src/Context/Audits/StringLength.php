<?php namespace Celestriode\TargetSelectorConstructure\Context\Audits;

use Celestriode\Constructure\AbstractConstructure;
use Celestriode\Constructure\Structures\StructureInterface;
use Celestriode\TargetSelectorConstructure\Structures\DynamicOptions\Values\StringValue;
use Celestriode\TargetSelectorConstructure\Structures\PlayerSelector;

/**
 * Checks if the provided input is some form of acceptable string (a target selector by name, or a string value) and
 * checks if the string's length is within an acceptable range.
 *
 * @package Celestriode\TargetSelectorConstructure\Context\Audits
 */
class StringLength extends NumericRange
{
    public const INCOMPATIBLE = '2299a2fb-731e-469d-b4ad-fcf43f9a826b';
    public const OUT_OF_RANGE = '48e73490-0934-44dc-b834-c176813c3788';

    /**
     * @inheritDoc
     */
    public function audit(AbstractConstructure $constructure, StructureInterface $input, StructureInterface $expected): bool
    {
        if ($input instanceof StringValue) {

            return $this->inRange($constructure, $input->getStringValue() ?? '', $input, $expected);
        }

        if ($input instanceof PlayerSelector) {

            return $this->inRange($constructure, $input->getPlayerName() ?? '', $input, $expected);
        }

        $constructure->getEventHandler()->trigger(self::INCOMPATIBLE, $this, $input, $expected);

        return false;
    }

    /**
     * Returns whether or not the string length is within the desired range.
     *
     * @param AbstractConstructure $constructure
     * @param string $string
     * @param StructureInterface $input
     * @param StructureInterface $expected
     * @return bool
     */
    protected function inRange(AbstractConstructure $constructure, string $string, StructureInterface $input, StructureInterface $expected): bool
    {
        $len = strlen($string);

        if (!$this->getBounds()->within((float)$len, $this->isInclusive())) {

            $constructure->getEventHandler()->trigger(self::OUT_OF_RANGE, $this, $input, $expected);

            return false;
        }

        return true;
    }

    /**
     * @inheritDoc
     */
    public static function getName(): string
    {
        return 'string_length';
    }
}