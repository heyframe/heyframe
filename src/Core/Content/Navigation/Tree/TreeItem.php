<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Navigation\Tree;

use HeyFrame\Core\Content\Navigation\NavigationEntity;
use HeyFrame\Core\Content\Navigation\NavigationException;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Struct\Struct;

#[Package('discovery')]
class TreeItem extends Struct
{
    /**
     * @internal public to allow AfterSort::sort()
     */
    public ?string $afterId = null;

    /**
     * @param TreeItem[] $children
     */
    public function __construct(
        protected ?NavigationEntity $navigation,
        protected array $children,
    ) {
        $this->afterId = $this->navigation?->getAfterNavigationId();
    }

    public function getId(): string
    {
        return $this->getNavigation()->getId();
    }

    public function setNavigation(NavigationEntity $navigation): void
    {
        $this->navigation = $navigation;
        $this->afterId = $navigation->getAfterNavigationId();
    }

    public function getNavigation(): NavigationEntity
    {
        if (!$this->navigation) {
            throw NavigationException::navigationNotFound('treeItem');
        }

        return $this->navigation;
    }

    /**
     * @return TreeItem[]
     */
    public function getChildren(): array
    {
        return $this->children;
    }

    public function addChildren(TreeItem ...$items): void
    {
        foreach ($items as $item) {
            $this->children[] = $item;
        }
    }

    /**
     * @param TreeItem[] $children
     */
    public function setChildren(array $children): void
    {
        $this->children = $children;
    }

    public function getApiAlias(): string
    {
        return 'navigation_tree_item';
    }
}
