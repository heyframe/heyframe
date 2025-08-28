<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Test\DataAbstractionLayer\Field\TestDefinition;

use HeyFrame\Core\Framework\DataAbstractionLayer\EntityExtension;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\BoolField;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Flag\ApiAware;
use HeyFrame\Core\Framework\DataAbstractionLayer\FieldCollection;

/**
 * @internal
 */
class ModifyFieldsExtension extends EntityExtension
{
    public function modifyFields(FieldCollection $collection): void
    {
        foreach ($collection as $field) {
            if ($field->getPropertyName() === 'apiAwareTest') {
                $field->removeFlag(ApiAware::class);
            }
        }

        // This is used to test if the collection is modifiable
        $collection->clear();
        $collection->add(new BoolField('newField', 'newField'));
    }

    public function getEntityName(): string
    {
        return 'extendable';
    }
}
