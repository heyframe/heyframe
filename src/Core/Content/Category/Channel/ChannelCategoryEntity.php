<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Category\Channel;

use HeyFrame\Core\Content\Category\CategoryEntity;
use HeyFrame\Core\Framework\Log\Package;

#[Package('discovery')]
class ChannelCategoryEntity extends CategoryEntity
{
    protected ?string $seoUrl = null;

    public function getSeoUrl(): ?string
    {
        return $this->seoUrl;
    }

    public function setSeoUrl(string $seoUrl): void
    {
        $this->seoUrl = $seoUrl;
    }
}
