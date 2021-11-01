<?php namespace Celestriode\TargetSelectorConstructure\Structures\DynamicOptions\Values;

use Celestriode\Constructure\Structures\AbstractStructure;
use Celestriode\TargetSelectorConstructure\Structures\DynamicOptions\Parameter;

/**
 * Parent class for parameter value types. All parameter types are Constructure structures to allow for comparison.
 *
 * @package Celestriode\TargetSelectorConstructure\Structures\DynamicOptions
 */
abstract class AbstractValue extends AbstractStructure
{
    /**
     * @var Parameter The parameter that this value belongs to.
     */
    private $parameter;

    /**
     * @var string|null The key of the value, should one exist..
     */
    private $key;

    /**
     * @var bool Whether or not the parameter is allowed to be negated.
     */
    protected $negatable = false;

    /**
     * @var bool Whether or not the parameter is actually negated.
     */
    protected $negated = false;

    /**
     * @var bool When true, there can be multiple of the same parameter.
     */
    protected $supportsMultiple = false;

    public function __construct(Parameter $parameter, $input = null)
    {
        $this->setParameter($parameter);

        parent::__construct($input);
    }

    /**
     * Returns the parameter that this value belongs to.
     *
     * @return Parameter
     */
    public function getParameter(): Parameter
    {
        return $this->parameter;
    }

    /**
     * Sets the parameter that this value belongs to to the supplied parameter.
     *
     * @param Parameter $parameter
     * @return $this
     */
    public function setParameter(Parameter $parameter): self
    {
        $this->parameter = $parameter;

        return $this;
    }

    /**
     * Marks the value as supporting multiple of the same parameter.
     *
     * @param bool $bl
     * @return $this
     */
    public function supportMultiple(bool $bl = true): self
    {
        $this->supportsMultiple = $bl;

        return $this;
    }

    /**
     * Returns whether or not the parameter may have more than one of this value.
     *
     * @return bool
     */
    public function supportsMultiple(): bool
    {
        return $this->supportsMultiple;
    }

    /**
     * Sets the key of the value.
     *
     * @param string|null $key The key to associate the value with.
     * @return $this
     */
    public function setKey(?string $key): self
    {
        $this->key = $key;

        return $this;
    }

    /**
     * Returns the key of the value, should one exist.
     *
     * @return string|null
     */
    public function getKey(): ?string
    {
        return $this->key;
    }

    /**
     * Sets the parameter as negatable.
     *
     * @param bool $negatable
     * @return $this
     */
    public function negatable(bool $negatable = true): self
    {
        $this->negatable = $negatable;

        return $this;
    }

    /**
     * Returns whether or not the parameter is negated.
     *
     * @return bool
     */
    public function isNegated(): bool
    {
        return $this->negated;
    }

    /**
     * Returns whether or not the parameter is allowed to be negated.
     *
     * @return bool
     */
    public function isNegatable(): bool
    {
        return $this->negatable;
    }

    /**
     * Sets the parameter as being negated.
     *
     * @param bool $negated
     * @return $this
     */
    public function setNegated(bool $negated = true): self
    {
        $this->negated = $negated;

        return $this;
    }

    /**
     * Returns whether or not the other value is compatible for comparison with this value.
     *
     * @param AbstractValue $other
     * @return bool
     */
    public function compatible(AbstractValue $other): bool
    {
        return get_class($other) == static::class;
    }
}