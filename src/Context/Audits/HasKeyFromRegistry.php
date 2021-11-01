<?php namespace Celestriode\TargetSelectorConstructure\Context\Audits;

use Celestriode\Constructure\Structures\StructureInterface;
use Celestriode\TargetSelectorConstructure\Structures\DynamicOptions\Parameter;
use Celestriode\TargetSelectorConstructure\Structures\DynamicOptions\Values\AbstractValue;
use InvalidArgumentException;

/**
 * Checks if the key of a parameter exists within the supplied registry.
 *
 * @package Celestriode\TargetSelectorConstructure\Context\Audits
 */
class HasKeyFromRegistry extends HasValueFromRegistry
{
    /**
     * @inheritDoc
     */
    protected function getValueFromInput(StructureInterface $input): string
    {
        if ($input instanceof Parameter || $input instanceof AbstractValue) {

            return $input->getKey();
        }

        throw new InvalidArgumentException('Invalid input');
    }

    /**
     * @inheritDoc
     */
    public static function getName(): string
    {
        return 'has_key_from_registry';
    }

    /**
     * @inheritDoc
     */
    public function toString(): string
    {
        return static::getName() . '{registry=' . $this->getRegistry()->getName() . ',lenient=' . (($this->lenient) ? 'true' : 'false') . '}';
    }
}