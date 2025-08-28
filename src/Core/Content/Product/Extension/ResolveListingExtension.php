<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Product\Extension;

use HeyFrame\Core\Content\Product\ProductCollection;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Criteria;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\EntitySearchResult;
use HeyFrame\Core\Framework\Extensions\Extension;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelContext;

/**
 * @codeCoverageIgnore
 *
 * @extends Extension<EntitySearchResult<ProductCollection>>
 */
#[Package('inventory')]
final class ResolveListingExtension extends Extension
{
    public const NAME = 'listing-loader.resolve';

    /**
     * @internal heyframe owns the __constructor, but the properties are public API
     */
    public function __construct(
        /**
         * @public
         *
         * @description The criteria which should be used to load the products. Is also containing the selected customer filter
         */
        public readonly Criteria $criteria,
        /**
         * @public
         *
         * @description Allows you to access to the current customer/sales-channel context
         */
        public readonly ChannelContext $context
    ) {
    }
}
