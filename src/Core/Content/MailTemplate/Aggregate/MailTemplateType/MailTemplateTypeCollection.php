<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\MailTemplate\Aggregate\MailTemplateType;

use HeyFrame\Core\Framework\DataAbstractionLayer\EntityCollection;
use HeyFrame\Core\Framework\Log\Package;

/**
 * @extends EntityCollection<MailTemplateTypeEntity>
 */
#[Package('after-sales')]
class MailTemplateTypeCollection extends EntityCollection
{
    public function getApiAlias(): string
    {
        return 'mail_template_type_collection';
    }

    protected function getExpectedClass(): string
    {
        return MailTemplateTypeEntity::class;
    }
}
