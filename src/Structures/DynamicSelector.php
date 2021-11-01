<?php namespace Celestriode\TargetSelectorConstructure\Structures;

use Celestriode\Constructure\AbstractConstructure;
use Celestriode\Constructure\Context\PrettifierInterface;
use Celestriode\Constructure\Structures\StructureInterface;
use Celestriode\DynamicRegistry\AbstractRegistry;
use Celestriode\TargetSelectorConstructure\Parsers\TargetSelectorParser;
use Celestriode\TargetSelectorConstructure\Structures\DynamicOptions\ParametersContainer;

/**
 * A target selector enclosure for dynamic selection through the use of target types and parameters.
 *
 * @package Celestriode\TargetSelectorConstructure\Structures
 */
class DynamicSelector extends AbstractTargetSelector
{
    public const INCOMPATIBLE = '0a8269ce-eb4d-469c-a597-7cb46398f335';

    /**
     * @var AbstractRegistry Array of target types.
     */
    protected $targetsRegistry;

    /**
     * @var ParametersContainer Collection of parameters.
     */
    protected $parameters;

    public function __construct(AbstractRegistry $targetsRegistry, ParametersContainer $parameters)
    {
        $this->targetsRegistry = $targetsRegistry;
        $this->parameters = $parameters;

        parent::__construct();
    }

    /**
     * @inheritDoc
     */
    public function compare(AbstractConstructure $constructure, StructureInterface $other): bool
    {
        if (!($other instanceof DynamicSelector)) {

            $constructure->getEventHandler()->trigger(self::INCOMPATIBLE, $other, $this);

            return false;
        }

        // Verify the target type.

        $inputTargetRegistry = $other->getTargetsRegistry();

        // If the input didn't actually have a target and no target was allowed, it should pass.

        if (empty($inputTargetRegistry->populate()->getValues()) && empty($this->getTargetsRegistry()->populate()->getValues())) {

            $targetsMatched = true;
        } else {

            $targetsMatched = $this->getTargetsRegistry()->has($inputTargetRegistry->getValues()[0] ?? null); // TODO: double nested check for any.
        }

        $parametersMatched = $this->getParameters()->compare($constructure, $other->getParameters());

        // Verify the parameters. Cycle through the input to find the parameter in the expected.

        return parent::compare($constructure, $other) && $targetsMatched && $parametersMatched;
    }

    /**
     * @return AbstractRegistry
     */
    public function getTargetsRegistry(): AbstractRegistry
    {
        return $this->targetsRegistry;
    }

    /**
     * @return ParametersContainer
     */
    public function getParameters(): ParametersContainer
    {
        return $this->parameters;
    }

    /**
     * @inheritDoc
     */
    public function getSelectorType(): string
    {
        return 'dynamic';
    }

    /**
     * @inheritDoc
     */
    public function toString(PrettifierInterface $prettifier = null): string
    {
        $targetTypes = $this->getTargetsRegistry()->populate()->getValues();

        if (count($targetTypes) == 1) {

            $buffer = TargetSelectorParser::TARGETER . $targetTypes[0];

            if (!$this->getParameters()->isEmpty()) {

                $buffer = $buffer . TargetSelectorParser::DELIMITER_OPEN . $this->getParameters()->toString($prettifier) . TargetSelectorParser::DELIMITER_CLOSE;
            }

            return $buffer;
        }

        return 'mixed_dynamic_selector';
    }
}