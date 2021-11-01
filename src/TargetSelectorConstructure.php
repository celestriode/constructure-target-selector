<?php namespace Celestriode\TargetSelectorConstructure;

use Celestriode\Captain\Exceptions\CommandSyntaxException;
use Celestriode\Constructure\AbstractConstructure;
use Celestriode\Constructure\Context\AuditInterface;
use Celestriode\Constructure\Context\Events\EventHandlerInterface;
use Celestriode\Constructure\Structures\StructureInterface;
use Celestriode\DynamicRegistry\Exception\InvalidValue;
use Celestriode\TargetSelectorConstructure\Exceptions\ConversionFailure;
use Celestriode\TargetSelectorConstructure\Parsers\TargetSelectorParser;
use Celestriode\TargetSelectorConstructure\Structures\Selector;

/**
 * A Constructure implementation for Minecraft's target selectors. Their structures are different between versions and
 * editions, and have nesting capabilities, making them prime material for such an implementation. Their overall scheme
 * has not changed much over time, other than allowing new structure types and parameters.
 *
 * @package Celestriode\TargetSelectorConstructure
 */
class TargetSelectorConstructure extends AbstractConstructure
{
    /**
     * @var TargetSelectorParser The parser to use during conversion.
     */
    private $parser;

    public function __construct(TargetSelectorParser $parser, EventHandlerInterface $eventHandler, AuditInterface ...$globalAudits)
    {
        $this->parser = $parser;

        parent::__construct($eventHandler, ...$globalAudits);
    }

    /**
     * Returns the target selector parser that this constructure object shall use during conversion.
     *
     * @return TargetSelectorParser
     */
    public function getParser(): TargetSelectorParser
    {
        return $this->parser;
    }

    /**
     * Converts a string into a target selector structure fit for Constructure comparison.
     *
     * @throws ConversionFailure
     */
    public function toStructure($input): StructureInterface
    {
        if (!is_string($input)) {

            throw new ConversionFailure("Raw input must be a string.");
        }

        try {

            // "Selector" is the root of any target selector.

            return new Selector($this->getParser()->parse($input));
        } catch (ConversionFailure|CommandSyntaxException|InvalidValue $e) {

            throw new ConversionFailure('Conversion failed: ' . $e->getMessage());
        }
    }
}