<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\MailTemplate;

use HeyFrame\Core\Framework\DataAbstractionLayer\EntityCollection;
use HeyFrame\Core\Framework\Log\Package;

/**
 * @extends EntityCollection<MailTemplateEntity>
 */
#[Package('after-sales')]
class MailTemplateCollection extends EntityCollection
{
    public function getApiAlias(): string
    {
        return 'mail_template_collection';
    }

    protected function getExpectedClass(): string
    {
        return MailTemplateEntity::class;
    }
}
