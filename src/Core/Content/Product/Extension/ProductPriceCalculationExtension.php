<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Product\Extension;

use HeyFrame\Core\Framework\DataAbstractionLayer\Entity;
use HeyFrame\Core\Framework\Extensions\Extension;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelContext;

/**
 * @public this class is used as type-hint for all event listeners, so the class string is "public consumable" API
 *
 * @title Calculation of product prices
 *
 * @description This event allows to intercept the product price calculation process.
 *
 * @codeCoverageIgnore
 *
 * @extends Extension<void>
 */
#[Package('inventory')]
final class ProductPriceCalculationExtension extends Extension
{
    public const NAME = 'product.calculate-prices';

    /**
     * @internal heyframe owns the __constructor, but the properties are public API
     */
    public function __construct(
        /**
         * @public
         *
         * @description The products which has to be calculated
         *
         * @var iterable<Entity> $products
         */
        public readonly iterable $products,

        /**
         * @public
         *
         * @description Allows you to access to the current customer/channel context
         */
        public readonly ChannelContext $context
    ) {
    }
}
