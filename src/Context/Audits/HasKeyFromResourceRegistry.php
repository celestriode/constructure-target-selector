<?php namespace Celestriode\TargetSelectorConstructure\Context\Audits;

use Celestriode\Captain\Exceptions\CommandSyntaxException;
use Celestriode\Captain\StringReader;
use Celestriode\Constructure\Structures\StructureInterface;
use Celestriode\Mattock\Parsers\Java\Utils\ResourceLocation;
use Celestriode\TargetSelectorConstructure\Structures\DynamicOptions\Parameter;
use Celestriode\TargetSelectorConstructure\Structures\DynamicOptions\Values\AbstractValue;
use InvalidArgumentException;

/**
 * Checks if the resource location of a parameter exists within the supplied registry.
 *
 * @package Celestriode\TargetSelectorConstructure\Context\Audits
 */
class HasKeyFromResourceRegistry extends HasValueFromResourceRegistry
{
    /**
     * @inheritDoc
     */
    protected function getValueFromInput(StructureInterface $input): string
    {
        if (!($input instanceof Parameter) && !($input instanceof AbstractValue)) {

            throw new InvalidArgumentException('Invalid input');
        }

        try {
            $rl = ResourceLocation::read(new StringReader($input->getKey() ?? ''), $this->checkForTag());

            return $rl->toString($this->checkForTag());
        } catch (CommandSyntaxException $e) {

            throw new InvalidArgumentException($e->getMessage());
        }
    }

    /**
     * @inheritDoc
     */
    public static function getName(): string
    {
        return 'has_key_from_resource_registry';
    }
}