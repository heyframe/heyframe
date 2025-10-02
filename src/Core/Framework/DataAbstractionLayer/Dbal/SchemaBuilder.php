<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\DataAbstractionLayer\Dbal;

use Doctrine\DBAL\Schema\PrimaryKeyConstraint;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Table;
use Doctrine\DBAL\Types\Types;
use HeyFrame\Core\Content\Flow\DataAbstractionLayer\Field\FlowTemplateConfigField;
use HeyFrame\Core\Content\Product\DataAbstractionLayer\CheapestPrice\CheapestPriceField;
use HeyFrame\Core\Framework\DataAbstractionLayer\DataAbstractionLayerException;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityDefinition;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\AssociationField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\AutoIncrementField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\BlobField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\BoolField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\BreadcrumbField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\CalculatedPriceField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\CartPriceField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\CashRoundingConfigField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\ChildCountField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\ChildrenAssociationField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\ConfigJsonField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\CreatedAtField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\CreatedByField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\CronIntervalField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\CustomFields;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\DateField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\DateIntervalField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\DateTimeField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\EmailField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\EnumField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\ExtraFields;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Field;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\FkField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\CascadeDelete;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\RestrictDelete;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\Runtime;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\FloatField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\IdField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\IntField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\JsonField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\ListField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\LockedField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\LongTextField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\ManyToManyIdField;
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
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\RemoteAddressField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\SerializedField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\StateMachineStateField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\StorageAware;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\StringField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\TaxFreeConfigField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\TimeZoneField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\TranslatedField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\TreeBreadcrumbField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\TreeLevelField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\TreePathField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\UpdatedAtField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\UpdatedByField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\VariantListingConfigField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\VersionDataPayloadField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\VersionField;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\NumberRange\DataAbstractionLayer\NumberRangeField;

/**
 * @internal
 */
#[Package('framework')]
class SchemaBuilder
{
    /**
     * @var array<class-string<Field>, Types::*>
     */
    public static array $fieldMapping = [
        IdField::class => Types::BINARY,
        FkField::class => Types::BINARY,
        ParentFkField::class => Types::BINARY,
        VersionField::class => Types::BINARY,
        ReferenceVersionField::class => Types::BINARY,
        CreatedByField::class => Types::BINARY,
        UpdatedByField::class => Types::BINARY,
        StateMachineStateField::class => Types::BINARY,

        CreatedAtField::class => Types::DATETIME_MUTABLE,
        UpdatedAtField::class => Types::DATETIME_MUTABLE,
        DateTimeField::class => Types::DATETIME_MUTABLE,

        DateField::class => Types::DATE_MUTABLE,
        SerializedField::class => Types::JSON,
        CartPriceField::class => Types::JSON,
        CalculatedPriceField::class => Types::JSON,
        PriceField::class => Types::JSON,
        PriceDefinitionField::class => Types::JSON,
        JsonField::class => Types::JSON,
        ListField::class => Types::JSON,
        ConfigJsonField::class => Types::JSON,
        CustomFields::class => Types::JSON,
        ExtraFields::class => Types::JSON,
        BreadcrumbField::class => Types::JSON,
        CashRoundingConfigField::class => Types::JSON,
        ObjectField::class => Types::JSON,
        TaxFreeConfigField::class => Types::JSON,
        TreeBreadcrumbField::class => Types::JSON,
        VariantListingConfigField::class => Types::JSON,
        VersionDataPayloadField::class => Types::JSON,
        ManyToManyIdField::class => Types::JSON,
        FlowTemplateConfigField::class => Types::JSON,
        CheapestPriceField::class => Types::JSON,

        ChildCountField::class => Types::INTEGER,
        IntField::class => Types::INTEGER,
        AutoIncrementField::class => Types::INTEGER,
        TreeLevelField::class => Types::INTEGER,

        BoolField::class => Types::BOOLEAN,
        LockedField::class => Types::BOOLEAN,

        PasswordField::class => Types::STRING,
        StringField::class => Types::STRING,
        TimeZoneField::class => Types::STRING,
        CronIntervalField::class => Types::STRING,
        DateIntervalField::class => Types::STRING,
        EmailField::class => Types::STRING,
        RemoteAddressField::class => Types::STRING,
        NumberRangeField::class => Types::STRING,

        BlobField::class => Types::BLOB,

        FloatField::class => Types::DECIMAL,

        TreePathField::class => Types::TEXT,
        LongTextField::class => Types::TEXT,
    ];

    /**
     * @var array{binary: array{length: 16, fixed: true}, boolean: array{default: 0}}
     */
    public static array $options = [
        Types::BINARY => [
            'length' => 16,
            'fixed' => true,
        ],

        Types::BOOLEAN => [
            'default' => 0,
        ],
    ];

    public function buildSchemaOfDefinition(EntityDefinition $definition): Table
    {
        $table = (new Schema())->createTable($definition->getEntityName());
        $table->addOption('charset', 'utf8mb4');
        $table->addOption('collate', 'utf8mb4_unicode_ci');

        foreach ($definition->getFields() as $field) {
            if ($field->is(Runtime::class)) {
                continue;
            }

            if ($field instanceof AssociationField) {
                continue;
            }

            if ($field instanceof TranslatedField) {
                continue;
            }

            if (!$field instanceof StorageAware) {
                continue;
            }

            $fieldType = $this->getFieldType($field);

            $table->addColumn(
                $field->getStorageName(),
                $fieldType,
                $this->getFieldOptions($field, $fieldType, $definition)
            );
        }

        $primaryKeys = $definition->getPrimaryKeys()->fmap(static function (Field $field): ?string {
            if ($field instanceof StorageAware) {
                return $field->getStorageName();
            }

            return null;
        });

        if ($primaryKeys) {
            $pk = PrimaryKeyConstraint::editor();
            $pk->setUnquotedColumnNames(...array_values($primaryKeys));
            $table->addPrimaryKeyConstraint($pk->create());
        }

        $this->addForeignKeys($table, $definition);

        return $table;
    }

    /**
     * @return Types::*
     */
    private function getFieldType(Field $field): string
    {
        if ($field instanceof EnumField) {
            return $field->getType();
        }

        foreach (self::$fieldMapping as $class => $type) {
            if ($field instanceof $class) {
                return self::$fieldMapping[$field::class];
            }
        }

        throw DataAbstractionLayerException::fieldHasNoType($field->getPropertyName());
    }

    /**
     * @return array<string, mixed>
     */
    private function getFieldOptions(Field $field, string $type, EntityDefinition $definition): array
    {
        $options = self::$options[$type] ?? [];

        $options['notnull'] = false;

        if ($field->is(Required::class) && !$field instanceof UpdatedAtField && !$field instanceof ReferenceVersionField) {
            $options['notnull'] = true;
        }

        if (\array_key_exists($field->getPropertyName(), $definition->getDefaults())) {
            $options['default'] = $definition->getDefaults()[$field->getPropertyName()];
        }

        if ($field instanceof StringField) {
            $options['length'] = $field->getMaxLength();
        }

        if ($field instanceof AutoIncrementField) {
            $options['autoincrement'] = true;
            $options['notnull'] = true;
        }

        if ($field instanceof FloatField) {
            $options['precision'] = 10;
            $options['scale'] = 2;
        }

        return $options;
    }

    private function addForeignKeys(Table $table, EntityDefinition $definition): void
    {
        $fields = $definition->getFields()->filter(
            function (Field $field) {
                if ($field instanceof ManyToOneAssociationField
                    || ($field instanceof OneToOneAssociationField && $field->getStorageName() !== 'id')) {
                    return true;
                }

                return false;
            }
        );

        $referenceVersionFields = $definition->getFields()->filterInstance(ReferenceVersionField::class);

        foreach ($fields as $field) {
            if (!$field instanceof ManyToOneAssociationField && !$field instanceof OneToOneAssociationField) {
                continue;
            }

            $reference = $field->getReferenceDefinition();

            $hasOneToMany = $definition->getFields()->filter(function (Field $field) use ($reference) {
                if (!$field instanceof OneToManyAssociationField) {
                    return false;
                }
                if ($field instanceof ChildrenAssociationField) {
                    return false;
                }

                return $field->getReferenceDefinition() === $reference;
            })->count() > 0;

            // skip foreign key to prevent bidirectional foreign key
            if ($hasOneToMany) {
                continue;
            }

            $columns = [
                $field->getStorageName(),
            ];

            $referenceColumns = [
                $field->getReferenceField(),
            ];

            if ($reference->isVersionAware()) {
                $versionField = null;

                foreach ($referenceVersionFields as $referenceVersionField) {
                    if (!$referenceVersionField instanceof ReferenceVersionField) {
                        continue;
                    }
                    if ($referenceVersionField->getVersionReferenceDefinition() === $reference) {
                        $versionField = $referenceVersionField;

                        break;
                    }
                }

                if ($field instanceof ParentAssociationField) {
                    $columns[] = 'version_id';
                } else {
                    if ($versionField === null) {
                        throw DataAbstractionLayerException::versionFieldNotFound($field->getPropertyName());
                    }

                    $columns[] = $versionField->getStorageName();
                }

                $referenceColumns[] = 'version_id';
            }

            $update = 'CASCADE';

            if ($field->is(CascadeDelete::class)) {
                $delete = 'CASCADE';
            } elseif ($field->is(RestrictDelete::class)) {
                $delete = 'RESTRICT';
            } else {
                $delete = 'SET NULL';
            }

            $table->addForeignKeyConstraint(
                $reference->getEntityName(),
                $columns,
                $referenceColumns,
                [
                    'onUpdate' => $update,
                    'onDelete' => $delete,
                ],
                \sprintf('fk.%s.%s', $definition->getEntityName(), $field->getStorageName())
            );
        }
    }
}
