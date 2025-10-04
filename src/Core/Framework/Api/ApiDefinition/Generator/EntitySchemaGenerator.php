<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Api\ApiDefinition\Generator;

use HeyFrame\Core\Framework\Api\ApiDefinition\ApiDefinitionGeneratorInterface;
use HeyFrame\Core\Framework\DataAbstractionLayer\Dbal\EntityDefinitionQueryHelper;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityDefinition;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityProtection\ReadProtection;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityProtection\WriteProtection;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\BlobField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\BoolField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\BreadcrumbField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\CalculatedPriceField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\CartPriceField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\ChildCountField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\ChildrenAssociationField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\CreatedAtField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\CustomFields;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\DateField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\DateTimeField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Field;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\FkField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\Flag;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\FloatField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\IdField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\IntField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\JsonField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\ListField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\LongTextField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\ManyToManyAssociationField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\ManyToOneAssociationField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\ObjectField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\OneToManyAssociationField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\OneToOneAssociationField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\ParentAssociationField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\ParentFkField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\PasswordField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\PriceDefinitionField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\PriceField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\ReferenceVersionField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\StringField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\TranslatedField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\TranslationsAssociationField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\TreeLevelField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\TreePathField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\UpdatedAtField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\VersionDataPayloadField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\VersionField;
use HeyFrame\Core\Framework\Log\Package;

/**
 * @internal
 */
#[Package('framework')]
class EntitySchemaGenerator implements ApiDefinitionGeneratorInterface
{
    final public const FORMAT = 'entity-schema';

    public function supports(string $format, string $api): bool
    {
        return $format === self::FORMAT;
    }

    public function generate(array $definitions, string $api, string $apiType = 'jsonapi', ?string $bundleName = null): never
    {
        throw new \RuntimeException();
    }

    /**
     * @return array<
     *     string,
     *     array{
     *          entity: string,
     *          properties: array<string, array{type: string, flags: array<string, mixed>}>,
     *          write-protected: bool,
     *          read-protected: bool,
     *          flags?: list<Flag>
     *      }
     * >
     */
    public function getSchema(array $definitions): array
    {
        $schema = [];

        ksort($definitions);

        foreach ($definitions as $definition) {
            $entity = $definition->getEntityName();

            $entitySchema = $this->getEntitySchema($definition);

            if ($entity === 'notification') {
                dd($entitySchema['read-protected']);
            }

            if ($entitySchema['write-protected'] && $entitySchema['read-protected']) {
                continue;
            }

            $schema[$entity] = $entitySchema;
        }

        return $schema;
    }

    /**
     * @return array{
     *     entity: string,
     *     properties: array<string, array{type: string, flags: array<string, mixed>}>,
     *     write-protected: bool,
     *     read-protected: bool,
     *     flags?: list<Flag>
     *  }
     */
    private function getEntitySchema(EntityDefinition $definition): array
    {
        $fields = $definition->getFields();

        $properties = [];
        foreach ($fields as $field) {
            $properties[$field->getPropertyName()] = $this->parseField($definition, $field);
        }

        return [
            'entity' => $definition->getEntityName(),
            'properties' => $properties,
            'write-protected' => $definition->getProtections()->get(WriteProtection::class) !== null,
            'read-protected' => $definition->getProtections()->get(ReadProtection::class) !== null,
        ];
    }

    /**
     * @return array{type: string, flags: array<string, mixed>}
     */
    private function parseField(EntityDefinition $definition, Field $field): array
    {
        $flags = [];
        foreach ($field->getFlags() as $flag) {
            $flags = array_replace_recursive($flags, iterator_to_array($flag->parse()));
        }

        switch (true) {
            case $field instanceof TranslatedField:
                $property = $this->parseField(
                    $definition,
                    EntityDefinitionQueryHelper::getTranslatedField($definition, $field)
                );
                $property['flags'] = array_replace_recursive($property['flags'], $flags);
                $property['flags']['translatable'] = true;

                return $property;

                // fields with uuid
            case $field instanceof VersionField:
            case $field instanceof ReferenceVersionField:
            case $field instanceof ParentFkField:
            case $field instanceof FkField:
            case $field instanceof IdField:
                return ['type' => 'uuid', 'flags' => $flags];

                // json fields
            case $field instanceof CustomFields:
            case $field instanceof VersionDataPayloadField:
            case $field instanceof CalculatedPriceField:
            case $field instanceof CartPriceField:
            case $field instanceof PriceDefinitionField:
            case $field instanceof PriceField:
            case $field instanceof ObjectField:
                return $this->createJsonObjectType($definition, $field, $flags);

            case $field instanceof ListField:
            case $field instanceof BreadcrumbField:
                return ['type' => 'json_list', 'flags' => $flags];

            case $field instanceof JsonField:
                return $this->createJsonObjectType($definition, $field, $flags);

                // association fields
            case $field instanceof ChildrenAssociationField:
            case $field instanceof TranslationsAssociationField:
            case $field instanceof OneToManyAssociationField:
                $reference = $field->getReferenceDefinition();
                $localField = $definition->getFields()->getByStorageName($field->getLocalField());
                $referenceField = $reference->getFields()->getByStorageName($field->getReferenceField());

                $primary = $reference->getPrimaryKeys()->first();
                if (!$primary) {
                    throw new \RuntimeException(\sprintf('No primary key defined for %s', $reference->getEntityName()));
                }

                return [
                    'type' => 'association',
                    'relation' => 'one_to_many',
                    'entity' => $reference->getEntityName(),
                    'flags' => $flags,
                    'localField' => $localField ? $localField->getPropertyName() : null,
                    'referenceField' => $referenceField ? $referenceField->getPropertyName() : null,
                    'primary' => $primary->getPropertyName(),
                ];

            case $field instanceof ParentAssociationField:
            case $field instanceof ManyToOneAssociationField:
                $reference = $field->getReferenceDefinition();
                $localField = $definition->getFields()->getByStorageName($field->getStorageName());
                $referenceField = $reference->getFields()->getByStorageName($field->getReferenceField());

                return [
                    'type' => 'association',
                    'relation' => 'many_to_one',
                    'entity' => $reference->getEntityName(),
                    'flags' => $flags,
                    'localField' => $localField ? $localField->getPropertyName() : null,
                    'referenceField' => $referenceField ? $referenceField->getPropertyName() : null,
                ];

            case $field instanceof ManyToManyAssociationField:
                $reference = $field->getToManyReferenceDefinition();
                $localField = $definition->getFields()->getByStorageName($field->getLocalField());
                $referenceField = $reference->getFields()->getByStorageName($field->getReferenceField());

                $mappingReference = $field->getMappingDefinition()->getFields()->getByStorageName(
                    $field->getMappingReferenceColumn()
                );
                $mappingLocal = $field->getMappingDefinition()->getFields()->getByStorageName(
                    $field->getMappingLocalColumn()
                );

                if (!$mappingReference) {
                    throw new \RuntimeException(\sprintf('Can not find mapping entity field for storage field %s', $field->getMappingReferenceColumn()));
                }
                if (!$mappingLocal) {
                    throw new \RuntimeException(\sprintf('Can not find mapping entity field for storage field %s', $field->getMappingLocalColumn()));
                }

                return [
                    'type' => 'association',
                    'relation' => 'many_to_many',
                    'local' => $mappingLocal->getPropertyName(),
                    'reference' => $mappingReference->getPropertyName(),
                    'mapping' => $field->getMappingDefinition()->getEntityName(),
                    'entity' => $field->getToManyReferenceDefinition()->getEntityName(),
                    'flags' => $flags,
                    'localField' => $localField ? $localField->getPropertyName() : null,
                    'referenceField' => $referenceField ? $referenceField->getPropertyName() : null,
                ];

            case $field instanceof OneToOneAssociationField:
                $reference = $field->getReferenceDefinition();

                $localField = $definition->getFields()->getByStorageName($field->getStorageName());
                $referenceField = $reference->getFields()->getByStorageName($field->getReferenceField());

                return [
                    'type' => 'association',
                    'relation' => 'one_to_one',
                    'entity' => $reference->getEntityName(),
                    'flags' => $flags,
                    'localField' => $localField ? $localField->getPropertyName() : null,
                    'referenceField' => $referenceField ? $referenceField->getPropertyName() : null,
                ];

                // int fields
            case $field instanceof ChildCountField:
            case $field instanceof TreeLevelField:
            case $field instanceof IntField:
                return ['type' => 'int', 'flags' => $flags];

                // long text fields
            case $field instanceof TreePathField:
            case $field instanceof LongTextField:
                return ['type' => 'text', 'flags' => $flags];

                // date fields
            case $field instanceof UpdatedAtField:
            case $field instanceof CreatedAtField:
            case $field instanceof DateTimeField:
            case $field instanceof DateField:
                return ['type' => 'date', 'flags' => $flags];

                // scalar fields
            case $field instanceof PasswordField:
                return ['type' => 'password', 'flags' => $flags];

            case $field instanceof FloatField:
                return ['type' => 'float', 'flags' => $flags];

            case $field instanceof StringField:
                return ['type' => 'string', 'flags' => $flags];

            case $field instanceof BlobField:
                return ['type' => 'blob', 'flags' => $flags];

            case $field instanceof BoolField:
                return ['type' => 'boolean', 'flags' => $flags];

            default:
                return ['type' => $field::class, 'flags' => $flags];
        }
    }

    /**
     * @param array<string, mixed> $flags
     *
     * @return array{
     *     type: string,
     *     properties: array<string,
     *     array{type: string, flags: array<string, mixed>}>,
     *     flags: array<string, mixed>
     * }
     */
    private function createJsonObjectType(EntityDefinition $definition, Field $field, array $flags): array
    {
        $nested = [];
        if ($field instanceof JsonField) {
            foreach ($field->getPropertyMapping() as $nestedField) {
                $nested[$nestedField->getPropertyName()] = $this->parseField($definition, $nestedField);
            }
        }

        return [
            'type' => 'json_object',
            'properties' => $nested,
            'flags' => $flags,
        ];
    }
}
