<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Seo\SeoUrlTemplate;

use HeyFrame\Core\Framework\DataAbstractionLayer\EntityCollection;
use HeyFrame\Core\Framework\Log\Package;

/**
 * @extends EntityCollection<SeoUrlTemplateEntity>
 */
#[Package('inventory')]
class SeoUrlTemplateCollection extends EntityCollection
{
    public function getApiAlias(): string
    {
        return 'seo_url_template_collection';
    }

    protected function getExpectedClass(): string
    {
        return SeoUrlTemplateEntity::class;
    }
}
