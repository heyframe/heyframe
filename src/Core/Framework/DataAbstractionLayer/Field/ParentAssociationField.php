<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\DataAbstractionLayer\Field;

use HeyFrame\Core\Framework\Log\Package;

#[Package('framework')]
class ParentAssociationField extends ManyToOneAssociationField
{
    public function __construct(
        string $referenceClass,
        string $referenceField = 'id'
    ) {
        parent::__construct('parent', 'parent_id', $referenceClass, $referenceField);
    }
}
