<?php namespace Celestriode\TargetSelectorConstructure\Context\Audits;

use Celestriode\DynamicRegistry\AbstractRegistry;

/**
 * Checks if the value of a parameter exists within the supplied registry.
 *
 * @package Celestriode\TargetSelectorConstructure\Context\Audits
 */
class HasValueFromRegistry extends HasValue
{
    /**
     * @var AbstractRegistry The registry to validate the value with.
     */
    protected $registry;

    public function __construct(AbstractRegistry $registry, bool $lenient = false)
    {
        $this->registry = $registry;

        parent::__construct($lenient);
    }

    /**
     * Returns the registry that will be used for validation.
     *
     * @return AbstractRegistry
     */
    public function getRegistry(): AbstractRegistry
    {
        return $this->registry;
    }

    /**
     * @inheritDoc
     */
    public function getAcceptedValues(): array
    {
        return $this->getRegistry()->populate()->getValues();
    }

    /**
     * @inheritDoc
     */
    protected function matches(string $input): bool
    {
        return $this->getRegistry()->has($input);
    }

    /**
     * @inheritDoc
     */
    public static function getName(): string
    {
        return 'has_value_from_registry';
    }

    /**
     * @inheritDoc
     */
    public function toString(): string
    {
        return static::getName() . '{registry=' . $this->getRegistry()->getName() . ',lenient=' . ($this->isLenient() ? 'true' : 'false') . '}';
    }
}