<?php namespace Celestriode\TargetSelectorConstructure\Structures\DynamicOptions\Values;

use Celestriode\Constructure\AbstractConstructure;
use Celestriode\Constructure\Context\PrettifierInterface;
use Celestriode\Constructure\Structures\StructureInterface;
use Celestriode\TargetSelectorConstructure\Parsers\TargetSelectorParser;
use Celestriode\TargetSelectorConstructure\Structures\DynamicOptions\Parameter;
use Celestriode\TargetSelectorConstructure\Structures\DynamicOptions\ParametersContainer;

/**
 * A value that has a nested list of parameters.
 *
 * @package Celestriode\TargetSelectorConstructure\Structures\DynamicOptions
 */
class ParametersValue extends AbstractValue
{
    public const INCOMPATIBLE = '6e5ff5cc-efcf-4f92-9ec8-f899702b0daa';

    public function __construct(Parameter $parameter, ParametersContainer $parameters)
    {
        parent::__construct($parameter, $parameters);
    }

    /**
     * Adds a parameter to the nested parameter container.
     *
     * @param string|null $key
     * @param AbstractValue $value
     * @return $this
     */
    public function addValue(?string $key, AbstractValue $value): self
    {
        $this->getParametersContainer()->addValue($key, $value);

        return $this;
    }

    /**
     * Returns the set of parameters for this value, or null if none were set.
     *
     * @return ParametersContainer
     */
    public function getParametersContainer(): ParametersContainer
    {
        return $this->getValue();
    }

    /**
     * @inheritDoc
     */
    public function compare(AbstractConstructure $constructure, StructureInterface $other): bool
    {
        if (!($other instanceof self)) {

            $constructure->getEventHandler()->trigger(self::INCOMPATIBLE, $other, $this);

            return false;
        }

        $result = true;

        // Check if the other has parameters.

        if ($other->getParametersContainer() !== null) {

            $result = $this->getParametersContainer()->compare($constructure, $other->getParametersContainer());
        }

        return parent::compare($constructure, $other) && $result;
    }

    /**
     * @inheritDoc
     */
    public function toString(PrettifierInterface $prettifier = null): string
    {
        return TargetSelectorParser::NESTED_DELIMITER_OPEN . $this->getParametersContainer()->toString($prettifier) . TargetSelectorParser::NESTED_DELIMITER_CLOSE;
    }
}