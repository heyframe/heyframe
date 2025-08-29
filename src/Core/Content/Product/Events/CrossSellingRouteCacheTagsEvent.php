<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Product\Events;

use HeyFrame\Core\Framework\Adapter\Cache\StoreApiRouteCacheTagsEvent;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Criteria;
use HeyFrame\Core\Framework\Feature;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelContext;
use HeyFrame\Core\System\Channel\FrontApiResponse;
use Symfony\Component\HttpFoundation\Request;

#[Package('inventory')]
/**
 * @deprecated tag:v6.8.0 - Will be removed in 6.8.0 as it was not used anymore
 */
class CrossSellingRouteCacheTagsEvent extends StoreApiRouteCacheTagsEvent
{
    public function __construct(
        protected string $productId,
        array $tags,
        Request $request,
        FrontApiResponse $response,
        ChannelContext $context,
        ?Criteria $criteria
    ) {
        Feature::triggerDeprecationOrThrow(
            'v6.8.0.0',
            Feature::deprecatedClassMessage(self::class, 'v6.8.0.0'),
        );

        parent::__construct($tags, $request, $response, $context, $criteria);
    }

    public function getProductId(): string
    {
        Feature::triggerDeprecationOrThrow(
            'v6.8.0.0',
            Feature::deprecatedClassMessage(self::class, 'v6.8.0.0'),
        );

        return $this->productId;
    }
}
