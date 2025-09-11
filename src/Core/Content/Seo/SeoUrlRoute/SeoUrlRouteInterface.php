<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Seo\SeoUrlRoute;

use HeyFrame\Core\Framework\DataAbstractionLayer\Entity;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Criteria;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelEntity;

#[Package('inventory')]
interface SeoUrlRouteInterface
{
    public function getConfig(): SeoUrlRouteConfig;

    public function prepareCriteria(Criteria $criteria, ChannelEntity $channel): void;

    public function getMapping(Entity $entity, ?ChannelEntity $channel): SeoUrlMapping;
}
