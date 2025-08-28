<?php declare(strict_types=1);

namespace HeyFrame\Core\System\CustomEntity\Schema;

use HeyFrame\Core\Framework\DataAbstractionLayer\EntityDefinition;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\ApiAware;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\Flag;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\IdField;
use HeyFrame\Core\Framework\DataAbstractionLayer\FieldCollection;
use HeyFrame\Core\Framework\Log\Package;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @internal The use of this class is reserved for the custom_entity feature
 *
 * @phpstan-import-type CustomEntityField from CustomEntitySchemaUpdater
 */
#[Package('framework')]
class DynamicEntityDefinition extends EntityDefinition
{
    protected string $name;

    /**
     * @var list<CustomEntityField>
     */
    protected array $fieldDefinitions;

    /**
     * @var list<Flag>
     */
    protected array $flags;

    protected ContainerInterface $container;

    /**
     * @param list<CustomEntityField> $fields
     * @param list<Flag> $flags
     */
    public static function create(
        string $name,
        array $fields,
        array $flags,
        ContainerInterface $container
    ): DynamicEntityDefinition {
        $self = new self();
        $self->name = $name;
        $self->fieldDefinitions = $fields;
        $self->container = $container;
        $self->flags = $flags;

        return $self;
    }

    public function getEntityName(): string
    {
        return $this->name;
    }

    /**
     * @return list<Flag>
     */
    public function getFlags(): array
    {
        return $this->flags;
    }

    public function getDefaults(): array
    {
        $values = [];
        foreach ($this->fieldDefinitions as $fieldDefinition) {
            if (!isset($fieldDefinition['default'])) {
                continue;
            }

            $values[$fieldDefinition['name']] = $fieldDefinition['default'];
        }

        return $values;
    }

    protected function defineFields(): FieldCollection
    {
        $collection = DynamicFieldFactory::create($this->container, $this->getEntityName(), $this->fieldDefinitions);

        $collection->add(
            (new IdField('id', 'id'))->addFlags(new ApiAware(), new Required(), new PrimaryKey()),
        );

        return $collection;
    }
}
