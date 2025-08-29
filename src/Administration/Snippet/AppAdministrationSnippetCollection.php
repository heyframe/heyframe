<?php declare(strict_types=1);

namespace HeyFrame\Administration\Snippet;

use HeyFrame\Core\Framework\DataAbstractionLayer\EntityCollection;
use HeyFrame\Core\Framework\Log\Package;

/**
 * @extends EntityCollection<AppAdministrationSnippetEntity>
 */
#[Package('discovery')]
class AppAdministrationSnippetCollection extends EntityCollection
{
    public function getApiAlias(): string
    {
        return 'administration_snippet_collection';
    }

    protected function getExpectedClass(): string
    {
        return AppAdministrationSnippetEntity::class;
    }
}
