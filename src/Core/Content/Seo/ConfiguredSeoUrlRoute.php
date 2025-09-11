<?php

declare(strict_types=1);

namespace HeyFrame\Core\Content\Seo;

use HeyFrame\Core\Content\Seo\SeoUrlRoute\SeoUrlMapping;
use HeyFrame\Core\Content\Seo\SeoUrlRoute\SeoUrlRouteConfig;
use HeyFrame\Core\Content\Seo\SeoUrlRoute\SeoUrlRouteInterface;
use HeyFrame\Core\Framework\DataAbstractionLayer\Entity;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Criteria;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelEntity;

#[Package('inventory')]
class ConfiguredSeoUrlRoute implements SeoUrlRouteInterface
{
    public function __construct(
        private readonly SeoUrlRouteInterface $decorated,
        private readonly SeoUrlRouteConfig $config
    ) {
    }

    public function getConfig(): SeoUrlRouteConfig
    {
        return $this->config;
    }

    public function prepareCriteria(Criteria $criteria, ChannelEntity $channel): void
    {
        $this->decorated->prepareCriteria($criteria, $channel);
    }

    public function getMapping(Entity $entity, ?ChannelEntity $channel): SeoUrlMapping
    {
        return $this->decorated->getMapping($entity, $channel);
    }
}
