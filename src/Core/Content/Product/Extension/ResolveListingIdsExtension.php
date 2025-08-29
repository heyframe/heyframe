<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Product\Extension;

use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Criteria;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\IdSearchResult;
use HeyFrame\Core\Framework\Extensions\Extension;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelContext;

/**
 * @public this class is used as type-hint for all event listeners, so the class string is "public consumable" API
 *
 * @title Determination of the listing product ids
 *
 * @description This event allows intercepting the listing process, when the product ids should be determined for the current category page and the applied filter.
 *
 * @codeCoverageIgnore
 *
 * @extends Extension<IdSearchResult>
 */
#[Package('inventory')]
final class ResolveListingIdsExtension extends Extension
{
    public const NAME = 'listing-loader.resolve-listing-ids';

    /**
     * @internal heyframe owns the __constructor, but the properties are public API
     */
    public function __construct(
        /**
         * @public
         *
         * @description The criteria which should be used to load the product ids. Is also containing the selected customer filter
         */
        public Criteria $criteria,

        /**
         * @public
         *
         * @description Allows you to access to the current customer/channel context
         */
        public ChannelContext $context
    ) {
    }
}
