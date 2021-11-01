<?php namespace Celestriode\TargetSelectorConstructure\Context\Audits;

use Celestriode\Constructure\AbstractConstructure;
use Celestriode\Constructure\Context\Audits\AbstractAudit;
use Celestriode\Constructure\Structures\StructureInterface;
use Celestriode\TargetSelectorConstructure\Structures\DynamicOptions\Parameter;

/**
 * Checks to make sure that there are no conflicting values. For example, having [type=pig,type=cow] does not work since
 * an entity cannot be both a pig and a cow. Note that this depends on the context within Minecraft, where [tag=a,tag=b]
 * does work, and so should not use this audit.
 *
 * @package Celestriode\TargetSelectorConstructure\Context\Audits
 */
class RestrictedNegation extends AbstractAudit
{
    public const INCOMPATIBLE = '27947cfc-ebf2-4091-bb04-18c47fb4990f';
    public const MIXED_NEGATIVE_WITH_NON = '12e305ba-4c8f-4c72-8dba-d693deca2bad';
    public const TOO_MANY_NON_NEGATIVE = '1d06c9f6-ce1e-46ce-a1a0-ccb22894d391';

    /**
     * @inheritDoc
     */
    public function audit(AbstractConstructure $constructure, StructureInterface $input, StructureInterface $expected): bool
    {
        if (!($input instanceof Parameter) || !($expected instanceof Parameter)) {

            $constructure->getEventHandler()->trigger(self::INCOMPATIBLE, $this, $input, $expected);

            return false;
        }

        $siblings = $input->getValues();

        $numNegated = 0;
        $numNotNegated = 0;

        // Count up the negated and non-negated values.

        foreach ($siblings as $sibling) {

            if ($sibling->isNegated()) {

                $numNegated++;
            } else {

                $numNotNegated++;
            }
        }

        // Cannot have a mix of negated and non-negated.

        if ($numNegated > 0 && $numNotNegated != 0) {

            $constructure->getEventHandler()->trigger(self::MIXED_NEGATIVE_WITH_NON, $this, $input, $expected);

            return false;
        }

        // Cannot have more than one non-negated.

        if ($numNotNegated > 1) {

            $constructure->getEventHandler()->trigger(self::TOO_MANY_NON_NEGATIVE, $this, $input, $expected);

            return false;
        }

        // Nothing wrong, audit passes.

        return true;
    }

    /**
     * @inheritDoc
     */
    public static function getName(): string
    {
        return 'restricted_negation';
    }
}