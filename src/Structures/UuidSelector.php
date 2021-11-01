<?php namespace Celestriode\TargetSelectorConstructure\Structures;

use Celestriode\Constructure\Context\PrettifierInterface;
use Ramsey\Uuid\UuidInterface;

/**
 * A target selector enclosure for UUID-based targeting.
 *
 * @package Celestriode\TargetSelectorConstructure\Structures
 */
class UuidSelector extends AbstractTargetSelector
{
    public function __construct(UuidInterface $uuid = null)
    {
        parent::__construct($uuid);
    }

    /**
     * Returns the value as a UUID object.
     *
     * @return UuidInterface|null
     */
    public function getUuid(): ?UuidInterface
    {
        return $this->getValue();
    }

    /**
     * @inheritDoc
     */
    public function getSelectorType(): string
    {
        return 'uuid';
    }

    /**
     * @inheritDoc
     */
    public function toString(PrettifierInterface $prettifier = null): string
    {
        return $this->getUuid()->toString();
    }
}