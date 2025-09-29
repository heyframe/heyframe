<?php declare(strict_types=1);

namespace HeyFrame\Frontend\Framework\Seo\SeoUrlRoute;

use HeyFrame\Core\Content\LandingPage\LandingPageDefinition;
use HeyFrame\Core\Content\LandingPage\LandingPageEntity;
use HeyFrame\Core\Content\Seo\SeoUrlRoute\SeoUrlMapping;
use HeyFrame\Core\Content\Seo\SeoUrlRoute\SeoUrlRouteConfig;
use HeyFrame\Core\Content\Seo\SeoUrlRoute\SeoUrlRouteInterface;
use HeyFrame\Core\Framework\DataAbstractionLayer\Entity;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Criteria;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelEntity;

#[Package('inventory')]
class LandingPageSeoUrlRoute implements SeoUrlRouteInterface
{
    final public const ROUTE_NAME = 'frontend.landing.page';
    final public const DEFAULT_TEMPLATE = '{{ landingPage.translated.url }}';

    /**
     * @internal
     */
    public function __construct(private readonly LandingPageDefinition $landingPageDefinition)
    {
    }

    public function getConfig(): SeoUrlRouteConfig
    {
        return new SeoUrlRouteConfig(
            $this->landingPageDefinition,
            self::ROUTE_NAME,
            self::DEFAULT_TEMPLATE,
            true
        );
    }

    public function prepareCriteria(Criteria $criteria, ChannelEntity $channel): void
    {
        $criteria->addFilter(new EqualsFilter('active', true));
        $criteria->addFilter(new EqualsFilter('channels.id', $channel->getId()));
    }

    public function getMapping(Entity $landingPage, ?ChannelEntity $channel): SeoUrlMapping
    {
        if (!$landingPage instanceof LandingPageEntity) {
            throw new \InvalidArgumentException('Expected LandingPageEntity');
        }

        $landingPageJson = $landingPage->jsonSerialize();

        return new SeoUrlMapping(
            $landingPage,
            ['landingPageId' => $landingPage->getId()],
            [
                'landingPage' => $landingPageJson,
            ]
        );
    }
}
