<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Navigation\Service;

use HeyFrame\Core\Content\Navigation\Channel\AbstractNavigationRoute;
use HeyFrame\Core\Content\Navigation\Event\NavigationLoadedEvent;
use HeyFrame\Core\Content\Navigation\Exception\NavigationNotFoundException;
use HeyFrame\Core\Content\Navigation\NavigationCollection;
use HeyFrame\Core\Content\Navigation\NavigationEntity;
use HeyFrame\Core\Content\Navigation\Tree\Tree;
use HeyFrame\Core\Content\Navigation\Tree\TreeItem;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Criteria;
use HeyFrame\Core\Framework\DataAbstractionLayer\Util\AfterSort;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelContext;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

#[Package('discovery')]
class NavigationLoader implements NavigationLoaderInterface
{
    private readonly TreeItem $treeItem;

    /**
     * @internal
     */
    public function __construct(
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly AbstractNavigationRoute $navigationRoute
    ) {
        $this->treeItem = new TreeItem(null, []);
    }

    /**
     * {@inheritdoc}
     *
     * @throws NavigationNotFoundException
     */
    public function load(string $activeId, ChannelContext $context, string $rootId, int $depth = 2): Tree
    {
        $request = new Request();
        $request->query->set('buildTree', 'false');
        $request->query->set('depth', (string) $depth);

        $criteria = new Criteria();
        $criteria->setTitle('header::navigation');

        $navigations = $this->navigationRoute
            ->load($activeId, $rootId, $request, $context, $criteria)
            ->getNavigations();

        $navigation = $this->getTree($rootId, $navigations, $navigations->get($activeId));

        $event = new NavigationLoadedEvent($navigation, $context);

        $this->eventDispatcher->dispatch($event);

        return $event->getNavigation();
    }

    private function getTree(?string $rootId, NavigationCollection $navigations, ?NavigationEntity $active): Tree
    {
        $parents = [];
        $items = [];
        foreach ($navigations as $navigation) {
            $item = clone $this->treeItem;
            $item->setNavigation($navigation);

            $parents[$navigation->getParentId()][$navigation->getId()] = $item;
            $items[$navigation->getId()] = $item;
        }

        foreach ($parents as $parentId => $children) {
            if (empty($parentId)) {
                continue;
            }

            $sorted = AfterSort::sort($children);

            $filtered = \array_filter($sorted, static fn (TreeItem $filter) => $filter->getNavigation()->getActive() && $filter->getNavigation()->getVisible());

            if (!isset($items[$parentId])) {
                continue;
            }

            $item = $items[$parentId];
            $item->setChildren($filtered);
        }

        $root = $parents[$rootId] ?? [];
        $root = AfterSort::sort($root);

        $filtered = [];
        /** @var TreeItem $item */
        foreach ($root as $key => $item) {
            if (!$item->getNavigation()->getActive() || !$item->getNavigation()->getVisible()) {
                continue;
            }

            $filtered[$key] = $item;
        }

        return new Tree($active, $filtered);
    }
}
