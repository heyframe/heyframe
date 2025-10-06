<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\MailTemplate\Aggregate\MailHeaderFooter;

use HeyFrame\Core\Framework\DataAbstractionLayer\EntityCollection;
use HeyFrame\Core\Framework\Log\Package;

/**
 * @extends EntityCollection<MailHeaderFooterEntity>
 */
#[Package('after-sales')]
class MailHeaderFooterCollection extends EntityCollection
{
    public function getApiAlias(): string
    {
        return 'mail_template_header_footer_collection';
    }

    protected function getExpectedClass(): string
    {
        return MailHeaderFooterEntity::class;
    }
}
