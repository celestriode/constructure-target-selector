<?php namespace Celestriode\TargetSelectorConstructure\Structures\DynamicOptions\Values;

use Celestriode\Constructure\Context\PrettifierInterface;
use Celestriode\TargetSelectorConstructure\Structures\DynamicOptions\Parameter;

/**
 * A simple value containing raw string data.
 *
 * @package Celestriode\TargetSelectorConstructure\Structures\DynamicOptions
 */
class StringValue extends AbstractValue
{
    public function __construct(Parameter $parameter, string $value = null)
    {
        parent::__construct($parameter, $value);
    }

    /**
     * Returns the value as a string, or null if the value doesn't exist.
     *
     * @return StringValue|null
     */
    public function getStringValue(): ?string
    {
        return $this->getValue();
    }

    /**
     * @inheritDoc
     */
    public function toString(PrettifierInterface $prettifier = null): string
    {
        return $this->getStringValue();
    }
}