<?php declare(strict_types=1);

namespace HeyFrame\Frontend\Page\Navigation;

use HeyFrame\Core\Content\Cms\CmsPageEntity;
use HeyFrame\Core\Content\Navigation\NavigationEntity;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Frontend\Page\Page;

#[Package('framework')]
class NavigationPage extends Page
{
    protected ?CmsPageEntity $cmsPage = null;

    protected ?NavigationEntity $navigation = null;

    protected ?string $navigationId = null;

    public function getCmsPage(): ?CmsPageEntity
    {
        return $this->cmsPage;
    }

    public function setCmsPage(?CmsPageEntity $cmsPage): void
    {
        $this->cmsPage = $cmsPage;
    }

    public function getNavigation(): ?NavigationEntity
    {
        return $this->navigation;
    }

    public function setNavigation(?NavigationEntity $navigation): void
    {
        $this->navigation = $navigation;
    }

    public function getNavigationId(): ?string
    {
        return $this->navigationId;
    }

    public function setNavigationId(?string $navigationId): void
    {
        $this->navigationId = $navigationId;
    }
}
