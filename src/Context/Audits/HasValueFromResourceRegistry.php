<?php namespace Celestriode\TargetSelectorConstructure\Context\Audits;

use Celestriode\Captain\Exceptions\CommandSyntaxException;
use Celestriode\Captain\StringReader;
use Celestriode\Constructure\Structures\StructureInterface;
use Celestriode\DynamicRegistry\AbstractRegistry;
use Celestriode\Mattock\Parsers\Java\Utils\ResourceLocation;
use InvalidArgumentException;

class HasValueFromResourceRegistry extends HasValueFromRegistry
{
    /**
     * @var bool Whether or not to check for a # at the beginning of the value to indicate a tag to check.
     */
    protected $checkForTag;

    /**
     * @var AbstractRegistry|null A tag registry for this audit, in the event the input is actually a tag.
     */
    protected $tagRegistry;

    /**
     * @var bool Whether or not the tag registry is leniently-checked.
     */
    protected $tagLenient;

    public function __construct(AbstractRegistry $registry, bool $lenient = false, bool $checkForTag = false, AbstractRegistry $tagRegistry = null, bool $tagLenient = true)
    {
        $this->checkForTag = $checkForTag;
        $this->tagRegistry = $tagRegistry;
        $this->tagLenient = $tagLenient;

        parent::__construct($registry, $lenient);
    }

    /**
     * Whether or not tagged resource locations are allowed.
     *
     * @return bool
     */
    public function checkForTag(): bool
    {
        return $this->checkForTag;
    }

    /**
     * Returns the tag registry, if it exists.
     *
     * @return AbstractRegistry|null
     */
    public function getTagRegistry(): ?AbstractRegistry
    {
        return $this->tagRegistry;
    }

    /**
     * Returns whether or not checking the tag registry should be done leniently.
     *
     * @return bool
     */
    public function isTagLenient(): bool
    {
        return $this->tagLenient;
    }

    /**
     * @inheritDoc
     */
    public function isLenient(string $value = null): bool
    {
        if ($value !== null && $this->checkForTag() && $this->isTagLenient() && $value[0] == ResourceLocation::TAG_TOKEN) {

            return true;
        }

        return parent::isLenient($value);
    }

    /**
     * @inheritDoc
     */
    protected function getValueFromInput(StructureInterface $input): string
    {
        $raw = parent::getValueFromInput($input);

        try {
            $rl = ResourceLocation::read(new StringReader($raw), $this->checkForTag());

            return $rl->toString($this->checkForTag());
        } catch (CommandSyntaxException $e) {

            throw new InvalidArgumentException($e->getMessage());
        }
    }

    /**
     * @inheritDoc
     */
    public function matches(string $input): bool
    {
        if ($this->checkForTag() && $this->getTagRegistry() !== null && $input[0] == ResourceLocation::TAG_TOKEN) {

            return $this->getTagRegistry()->has(mb_substr($input, 1));
        }

        return parent::matches($input);
    }

    /**
     * @inheritDoc
     */
    public static function getName(): string
    {
        return 'has_value_from_resource_registry';
    }

    /**
     * @inheritDoc
     */
    public function toString(): string
    {
        $tag = $this->getTagRegistry();
        $buffer = static::getName() . '{registry=' . $this->getRegistry()->getName() . ',lenient=' . ($this->isLenient() ? 'true' : 'false');

        if ($tag !== null) {

            return $buffer . ',tag_registry=' . $tag->getName() . ',tag_lenient=' . ($this->isTagLenient() ? 'true' : 'false') . '}';
        }

        return $buffer . '}';
    }
}