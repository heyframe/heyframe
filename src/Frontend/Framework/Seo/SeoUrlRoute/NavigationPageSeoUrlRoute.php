<?php declare(strict_types=1);

namespace HeyFrame\Frontend\Framework\Seo\SeoUrlRoute;

use HeyFrame\Core\Content\Navigation\NavigationDefinition;
use HeyFrame\Core\Content\Navigation\NavigationEntity;
use HeyFrame\Core\Content\Navigation\Service\NavigationBreadcrumbBuilder;
use HeyFrame\Core\Content\Seo\SeoUrlRoute\SeoUrlMapping;
use HeyFrame\Core\Content\Seo\SeoUrlRoute\SeoUrlRouteConfig;
use HeyFrame\Core\Content\Seo\SeoUrlRoute\SeoUrlRouteInterface;
use HeyFrame\Core\Framework\DataAbstractionLayer\Entity;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Criteria;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Filter\MultiFilter;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Filter\NotFilter;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelEntity;

#[Package('inventory')]
class NavigationPageSeoUrlRoute implements SeoUrlRouteInterface
{
    final public const ROUTE_NAME = 'frontend.navigation.page';
    final public const DEFAULT_TEMPLATE = '{% for part in navigation.seoBreadcrumb %}{{ part }}/{% endfor %}';

    /**
     * @internal
     */
    public function __construct(
        private readonly NavigationDefinition $navigationDefinition,
        private readonly NavigationBreadcrumbBuilder $breadcrumbBuilder
    ) {
    }

    public function getConfig(): SeoUrlRouteConfig
    {
        return new SeoUrlRouteConfig(
            $this->navigationDefinition,
            self::ROUTE_NAME,
            self::DEFAULT_TEMPLATE,
            true
        );
    }

    public function prepareCriteria(Criteria $criteria, ChannelEntity $channel): void
    {
        $criteria->addFilter(new MultiFilter(MultiFilter::CONNECTION_AND, [
            new EqualsFilter('active', true),
            new NotFilter(NotFilter::CONNECTION_OR, [
                new EqualsFilter('type', NavigationDefinition::TYPE_FOLDER),
                new EqualsFilter('type', NavigationDefinition::TYPE_LINK),
            ]),
        ]));
    }

    public function getMapping(Entity $navigation, ?ChannelEntity $channel): SeoUrlMapping
    {
        if (!$navigation instanceof NavigationEntity) {
            throw new \InvalidArgumentException('Expected NavigationEntity');
        }

        $rootId = $this->detectRootId($navigation, $channel);

        $breadcrumbs = $this->breadcrumbBuilder->build($navigation, $channel, $rootId);
        $navigationJson = $navigation->jsonSerialize();
        $navigationJson['seoBreadcrumb'] = $breadcrumbs;

        $error = null;
        if (!$rootId) {
            $error = 'Navigation is not available for sales channel';
        }

        return new SeoUrlMapping(
            $navigation,
            ['navigationId' => $navigation->getId()],
            [
                'navigation' => $navigationJson,
            ],
            $error
        );
    }

    private function detectRootId(NavigationEntity $navigation, ?ChannelEntity $channel): ?string
    {
        if (!$channel) {
            return null;
        }
        $path = array_filter(explode('|', (string) $navigation->getPath()));

        $navigationId = $channel->getNavigationNavigationId();
        if ($navigationId === $navigation->getId() || \in_array($navigationId, $path, true)) {
            return $navigationId;
        }

        $footerId = $channel->getFooterNavigationId();
        if ($footerId === $navigation->getId() || \in_array($footerId, $path, true)) {
            return $footerId;
        }

        $serviceId = $channel->getServiceNavigationId();
        if ($serviceId === $navigation->getId() || \in_array($serviceId, $path, true)) {
            return $serviceId;
        }

        return null;
    }
}
