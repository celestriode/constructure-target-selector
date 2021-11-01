<?php namespace Celestriode\TargetSelectorConstructure\Structures;

use Celestriode\Constructure\Structures\AbstractStructure;

/**
 * All variants of target selector types (player, UUID, dynamic, etc.) should extend this class.
 *
 * @package Celestriode\TargetSelectorConstructure\Structures
 */
abstract class AbstractTargetSelector extends AbstractStructure
{
    /**
     * Returns an identifier for the target selector to compare it to another for equivalency and display.
     *
     * @return string
     */
    abstract public function getSelectorType(): string;
}