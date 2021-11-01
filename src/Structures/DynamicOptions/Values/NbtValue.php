<?php namespace Celestriode\TargetSelectorConstructure\Structures\DynamicOptions\Values;

use Celestriode\Constructure\Context\PrettifierInterface;
use Celestriode\Mattock\Parsers\Java\Nbt\Tags\CompoundTag;
use Celestriode\TargetSelectorConstructure\Structures\DynamicOptions\Parameter;

/**
 * A value containing NBT data.
 *
 * @package Celestriode\TargetSelectorConstructure\Structures\DynamicOptions
 */
class NbtValue extends AbstractValue
{
    public function __construct(Parameter $parameter, CompoundTag $nbt = null)
    {
        parent::__construct($parameter, $nbt);
    }

    /**
     * Returns the NBT data as an NBT compound (or null if no value was set).
     *
     * @return CompoundTag|null
     */
    public function getNbtValue(): ?CompoundTag
    {
        return $this->getValue();
    }

    /**
     * @inheritDoc
     */
    public function toString(PrettifierInterface $prettifier = null): string
    {
        return $this->getNbtValue()->toString();
    }
}