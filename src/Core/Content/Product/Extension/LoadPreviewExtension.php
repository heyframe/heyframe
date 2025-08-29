<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Product\Extension;

use HeyFrame\Core\Framework\Extensions\Extension;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelContext;

/**
 * @public this class is used as type-hint for all event listeners, so the class string is "public consumable" API
 *
 * @title Determination of the preview loading in product listing
 *
 * @description This event allows intercepting the loading preview of product listing when the product IDs should be determined
 *
 * @codeCoverageIgnore
 *
 * @extends Extension<array>
 */
#[Package('inventory')]
final class LoadPreviewExtension extends Extension
{
    public const NAME = 'listing-loader.load-previews';

    /**
     * @internal heyframe owns the __constructor, but the properties are public API
     */
    public function __construct(
        /**
         * @public
         *
         * @description The array should contain a list of product ids.
         *
         * @var array<string>
         */
        public readonly array $ids,
        /**
         * @public
         *
         * @description Allows you to access to the current customer/channel context
         */
        public readonly ChannelContext $context
    ) {
    }
}
