<?php namespace Celestriode\TargetSelectorConstructure\Context\Audits;

use Celestriode\Constructure\AbstractConstructure;
use Celestriode\Constructure\Context\Audits\AbstractAudit;
use Celestriode\Constructure\Structures\StructureInterface;
use Celestriode\TargetSelectorConstructure\Structures\DynamicOptions\Parameter;
use Celestriode\TargetSelectorConstructure\Structures\DynamicOptions\Values\StringValue;
use InvalidArgumentException;

/**
 * Checks if the input has a value from the supplied list of values.
 *
 * @package Celestriode\TargetSelectorConstructure\Context\Audits
 */
class HasValue extends AbstractAudit
{
    public const INCOMPATIBLE = '658db722-a82f-4cfb-a844-938c116c4c1f';
    public const INVALID_VALUE = 'a99e6a14-deab-48a2-9e85-b4bcf80ab5b0';
    public const INVALID_VALUE_LENIENT = '4562d29e-5777-480b-85a3-f6f4c4d33dc1';

    /**
     * @var array A list of possible values that the input must match against.
     */
    protected $values = [];

    /**
     * @var bool Whether or not custom values are allowed.
     */
    protected $lenient;

    public function __construct(bool $lenient, string ...$values)
    {
        $this->values = $values;
        $this->lenient = $lenient;
    }

    /**
     * Returns the acceptable values that the input should match.
     *
     * @return array
     */
    public function getAcceptedValues(): array
    {
        return $this->values;
    }

    /**
     * Returns whether or not the input is allowed to be different from the acceptable values. This can be used to
     * run a separate event.
     *
     * @param string|null $value The value being checked for leniency.
     * @return bool
     */
    public function isLenient(string $value = null): bool
    {
        return $this->lenient;
    }

    /**
     * @inheritDoc
     */
    public function audit(AbstractConstructure $constructure, StructureInterface $input, StructureInterface $expected): bool
    {
        try {

            $value = $this->getValueFromInput($input);

            if (!$this->matches($value)) {

                if ($this->isLenient($value)) {

                    $constructure->getEventHandler()->trigger(self::INVALID_VALUE_LENIENT, $this, $input, $expected, $value);

                    return true;
                }

                $constructure->getEventHandler()->trigger(self::INVALID_VALUE, $this, $input, $expected, $value);

                return false;
            }
        } catch (InvalidArgumentException $e) {

            $constructure->getEventHandler()->trigger(self::INCOMPATIBLE, $this, $input, $expected, $e);

            return false;
        }

        return true;
    }

    /**
     * Takes in a structure and returns a string based on that structure.
     *
     * If the structure is a parameter, it will return its key.
     * If the structure is a string value, it will returns its value.
     *
     * Otherwise it will throw an InvalidArgumentException.
     *
     * @param StructureInterface $input
     * @throws InvalidArgumentException
     * @return string
     */
    protected function getValueFromInput(StructureInterface $input): string
    {
        if ($input instanceof Parameter) {

            return $input->getKey();
        } else if ($input instanceof StringValue) {

            return $input->getStringValue();
        }

        throw new InvalidArgumentException('Invalid input');
    }

    /**
     * Returns whether or not the input is present within the list of accepted values..
     *
     * @param string $input
     * @return bool
     */
    protected function matches(string $input): bool
    {
        return in_array($input, $this->getAcceptedValues());
    }

    /**
     * @inheritDoc
     */
    public static function getName(): string
    {
        return 'has_value';
    }

    /**
     * @inheritDoc
     */
    public function toString(): string
    {
        return parent::toString() . '{values=[' . implode(',', $this->getAcceptedValues()) . '],lenient=' . ($this->isLenient() ? 'true' : 'false') . '}';
    }
}