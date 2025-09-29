<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Navigation\Channel;

use HeyFrame\Core\Content\Navigation\NavigationEntity;
use HeyFrame\Core\Framework\Log\Package;

#[Package('discovery')]
class ChannelNavigationEntity extends NavigationEntity
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
