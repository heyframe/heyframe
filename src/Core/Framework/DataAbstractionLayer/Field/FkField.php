<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\DataAbstractionLayer\Field;

use HeyFrame\Core\Framework\DataAbstractionLayer\DefinitionInstanceRegistry;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityDefinition;
use HeyFrame\Core\Framework\DataAbstractionLayer\FieldSerializer\FkFieldSerializer;
use HeyFrame\Core\Framework\Log\Package;

#[Package('framework')]
class FkField extends Field implements StorageAware
{
    final public const PRIORITY = 70;

    protected ?EntityDefinition $referenceDefinition = null;

    protected ?DefinitionInstanceRegistry $registry = null;

    private ?string $referenceEntity = null;

    public function __construct(
        protected string $storageName,
        string $propertyName,
        protected string $referenceClass,
        protected string $referenceField = 'id'
    ) {
        parent::__construct($propertyName);
    }

    public function compile(DefinitionInstanceRegistry $registry): void
    {
        if ($this->registry !== null) {
            return;
        }

        $this->registry = $registry;

        parent::compile($registry);
    }

    public function getStorageName(): string
    {
        return $this->storageName;
    }

    public function getReferenceDefinition(): EntityDefinition
    {
        if ($this->referenceDefinition === null) {
            $this->compileLazy();
            \assert($this->referenceDefinition !== null);
        }

        return $this->referenceDefinition;
    }

    public function getReferenceField(): string
    {
        return $this->referenceField;
    }

    public function getExtractPriority(): int
    {
        return self::PRIORITY;
    }

    public function getReferenceEntity(): ?string
    {
        if ($this->referenceEntity === null) {
            $this->compileLazy();
        }

        return $this->referenceEntity;
    }

    protected function getSerializerClass(): string
    {
        return FkFieldSerializer::class;
    }

    protected function compileLazy(): void
    {
        \assert($this->registry !== null, 'registry could not be null, because the `compile` method must be called first');

        $this->referenceDefinition = $this->registry->getByClassOrEntityName($this->referenceClass);
        $this->referenceEntity = $this->referenceDefinition->getEntityName();
    }
}
