<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\DataAbstractionLayer\Field;

use HeyFrame\Core\Framework\DataAbstractionLayer\Dbal\FieldResolver\OneToManyAssociationFieldResolver;
use HeyFrame\Core\Framework\DataAbstractionLayer\FieldSerializer\OneToManyAssociationFieldSerializer;
use HeyFrame\Core\Framework\Log\Package;

#[Package('framework')]
class OneToManyAssociationField extends AssociationField
{
    public function __construct(
        string $propertyName,
        string $referenceClass,
        string $referenceField,
        protected string $localField = 'id',
    ) {
        parent::__construct($propertyName);
        $this->referenceField = $referenceField;
        $this->referenceClass = $referenceClass;
    }

    public function getLocalField(): string
    {
        return $this->localField;
    }

    protected function getSerializerClass(): string
    {
        return OneToManyAssociationFieldSerializer::class;
    }

    protected function getResolverClass(): ?string
    {
        return OneToManyAssociationFieldResolver::class;
    }
}
