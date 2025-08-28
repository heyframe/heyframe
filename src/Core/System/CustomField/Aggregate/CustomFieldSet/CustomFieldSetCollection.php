<?php declare(strict_types=1);

namespace HeyFrame\Core\System\CustomField\Aggregate\CustomFieldSet;

use HeyFrame\Core\Framework\DataAbstractionLayer\EntityCollection;
use HeyFrame\Core\Framework\Log\Package;

/**
 * @extends EntityCollection<CustomFieldSetEntity>
 */
#[Package('framework')]
class CustomFieldSetCollection extends EntityCollection
{
    public function getApiAlias(): string
    {
        return 'custom_field_set_collection';
    }

    protected function getExpectedClass(): string
    {
        return CustomFieldSetEntity::class;
    }
}
