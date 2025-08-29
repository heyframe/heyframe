<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Test;

use HeyFrame\Core\Content\Category\CategoryDefinition;
use HeyFrame\Core\Content\Category\CategoryEntity;
use HeyFrame\Core\Content\Seo\SeoUrlRoute\SeoUrlMapping;
use HeyFrame\Core\Content\Seo\SeoUrlRoute\SeoUrlRouteConfig;
use HeyFrame\Core\Content\Seo\SeoUrlRoute\SeoUrlRouteInterface;
use HeyFrame\Core\Framework\DataAbstractionLayer\Entity;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Criteria;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use HeyFrame\Core\Framework\Routing\FrontApiRouteScope;
use HeyFrame\Core\PlatformRequest;
use HeyFrame\Core\System\Channel\ChannelEntity;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * @internal
 */
#[Route(defaults: [PlatformRequest::ATTRIBUTE_ROUTE_SCOPE => [FrontApiRouteScope::ID]])]
class TestNavigationSeoUrlRoute implements SeoUrlRouteInterface
{
    final public const ROUTE_NAME = 'test.navigation.page';
    final public const DEFAULT_TEMPLATE = '{{ id }}';

    public function __construct(private readonly CategoryDefinition $categoryDefinition)
    {
    }

    #[Route(path: '/test/{navigationId}', name: 'test.navigation.page', options: ['seo' => true], methods: ['GET'])]
    public function route(): Response
    {
        return new Response();
    }

    public function getConfig(): SeoUrlRouteConfig
    {
        return new SeoUrlRouteConfig(
            $this->categoryDefinition,
            self::ROUTE_NAME,
            self::DEFAULT_TEMPLATE,
            true
        );
    }

    public function prepareCriteria(Criteria $criteria, ChannelEntity $channel): void
    {
        $criteria->addFilter(new EqualsFilter('active', true));
    }

    /**
     * @param CategoryEntity $entity
     */
    public function getMapping(Entity $entity, ?ChannelEntity $channel): SeoUrlMapping
    {
        return new SeoUrlMapping(
            $entity,
            ['navigationId' => $entity->getId()],
            ['id' => $entity->getId()]
        );
    }
}
