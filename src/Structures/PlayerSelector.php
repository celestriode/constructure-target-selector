<?php namespace Celestriode\TargetSelectorConstructure\Structures;

use Celestriode\Constructure\Context\PrettifierInterface;

/**
 * A target selector enclosure for name-based targeting.
 *
 * @package Celestriode\TargetSelectorConstructure\Structures
 */
class PlayerSelector extends AbstractTargetSelector
{
    public function __construct(string $name = null)
    {
        parent::__construct($name);
    }

    /**
     * Returns the value as a string.
     *
     * @return string|null
     */
    public function getPlayerName(): ?string
    {
        return $this->getValue();
    }

    /**
     * @inheritDoc
     */
    public function getSelectorType(): string
    {
        return 'player_name';
    }

    /**
     * @inheritDoc
     */
    public function toString(PrettifierInterface $prettifier = null): string
    {
        return $this->getPlayerName() ?? '';
    }
}