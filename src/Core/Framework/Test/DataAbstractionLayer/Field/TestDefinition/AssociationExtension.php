<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Test\DataAbstractionLayer\Field\TestDefinition;

use HeyFrame\Core\Framework\DataAbstractionLayer\EntityExtension;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\ApiAware;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\OneToManyAssociationField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\OneToOneAssociationField;
use HeyFrame\Core\Framework\DataAbstractionLayer\FieldCollection;

/**
 * @internal
 */
class AssociationExtension extends EntityExtension
{
    public function extendFields(FieldCollection $collection): void
    {
        $collection->add(
            (new OneToManyAssociationField('toMany', ExtendedDefinition::class, 'extendable_id'))
                ->addFlags(new ApiAware())
        );

        $collection->add(
            (new OneToOneAssociationField('toOne', 'id', 'extendable_id', ExtendedDefinition::class, false))
                ->addFlags(new ApiAware())
        );

        $collection->add(
            (new OneToOneAssociationField('toOneWithoutApiAware', 'id', 'extendable_id', ExtendedDefinition::class, false))
                ->removeFlag(ApiAware::class)
        );
    }

    public function getEntityName(): string
    {
        return 'extendable';
    }
}
