<?php
declare(strict_types=1);

namespace HeyFrame\Core\Framework\DataAbstractionLayer\Cache;

use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Criteria;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Util\Hasher;
use HeyFrame\Core\System\Channel\ChannelContext;

#[Package('framework')]
class EntityCacheKeyGenerator
{
    public static function buildCmsTag(string $id): string
    {
        return 'cms-page-' . $id;
    }

    public static function buildProductTag(string $id): string
    {
        return 'product-' . $id;
    }

    public static function buildStreamTag(string $id): string
    {
        return 'product-stream-' . $id;
    }

    /**
     * @param string[] $areas
     */
    public function getChannelContextHash(ChannelContext $context, array $areas = []): string
    {
        $ruleIds = $context->getRuleIdsByAreas($areas);

        return Hasher::hash([
            $context->getChannelId(),
            $context->getDomainId(),
            $context->getLanguageIdChain(),
            $context->getVersionId(),
            $context->getCurrencyId(),
            $context->getItemRounding(),
            $ruleIds,
        ]);
    }

    public function getCriteriaHash(Criteria $criteria): string
    {
        return Hasher::hash([
            $criteria->getIds(),
            $criteria->getFilters(),
            $criteria->getTerm(),
            $criteria->getPostFilters(),
            $criteria->getQueries(),
            $criteria->getSorting(),
            $criteria->getLimit(),
            $criteria->getOffset() ?? 0,
            $criteria->getTotalCountMode(),
            $criteria->getGroupFields(),
            $criteria->getAggregations(),
            $criteria->getAssociations(),
        ]);
    }
}
