<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Navigation\Tree;

use HeyFrame\Core\Content\Navigation\NavigationEntity;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Struct\Struct;

#[Package('discovery')]
class Tree extends Struct
{
    /**
     * @param TreeItem[] $tree
     */
    public function __construct(
        protected ?NavigationEntity $active,
        protected array $tree,
    ) {
    }

    public function isSelected(NavigationEntity $navigation): bool
    {
        if ($this->active === null) {
            return false;
        }

        if ($navigation->getId() === $this->active->getId()) {
            return true;
        }

        if (!$this->active->getPath()) {
            return false;
        }

        $ids = explode('|', $this->active->getPath());

        return \in_array($navigation->getId(), $ids, true);
    }

    /**
     * @return TreeItem[]
     */
    public function getTree(): array
    {
        return $this->tree;
    }

    /**
     * @param TreeItem[] $tree
     */
    public function setTree(array $tree): void
    {
        $this->tree = $tree;
    }

    public function getActive(): ?NavigationEntity
    {
        return $this->active;
    }

    public function setActive(?NavigationEntity $active): void
    {
        $this->active = $active;
    }

    public function getChildren(string $navigationId): ?Tree
    {
        $match = $this->find($navigationId, $this->tree);

        if ($match) {
            return new Tree($match->getNavigation(), $match->getChildren());
        }

        // active id is not part of $this->tree? active id is root or used as first level
        if ($this->active && $this->active->getId() === $navigationId) {
            return $this;
        }

        return null;
    }

    public function getApiAlias(): string
    {
        return 'navigation_tree';
    }

    /**
     * @param TreeItem[] $tree
     */
    private function find(string $navigationId, array $tree): ?TreeItem
    {
        if (isset($tree[$navigationId])) {
            return $tree[$navigationId];
        }

        foreach ($tree as $item) {
            $nested = $this->find($navigationId, $item->getChildren());

            if ($nested) {
                return $nested;
            }
        }

        return null;
    }
}
