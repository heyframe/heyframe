<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Product\Hook\Pricing;

use HeyFrame\Core\Checkout\Cart\Facade\PriceFactoryFactory;
use HeyFrame\Core\Framework\DataAbstractionLayer\Facade\ChannelRepositoryFacadeHookFactory;
use HeyFrame\Core\Framework\DataAbstractionLayer\Facade\RepositoryFacadeHookFactory;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Script\Execution\Awareness\ChannelContextAware;
use HeyFrame\Core\Framework\Script\Execution\Hook;
use HeyFrame\Core\System\Channel\ChannelContext;
use HeyFrame\Core\System\SystemConfig\Facade\SystemConfigFacadeHookFactory;

/**
 * Triggered when product prices are calculated for the store
 *
 * @hook-use-case product
 *
 * @since 6.5.1.0
 *
 * @final
 */
#[Package('inventory')]
class ProductPricingHook extends Hook implements ChannelContextAware
{
    final public const HOOK_NAME = 'product-pricing';

    /**
     * @param ProductProxy[] $products
     *
     * @internal
     */
    public function __construct(
        private readonly array $products,
        private readonly ChannelContext $channelContext
    ) {
        parent::__construct($this->channelContext->getContext());
    }

    /**
     * @return ProductProxy[]
     */
    public function getProducts(): iterable
    {
        return $this->products;
    }

    public static function getServiceIds(): array
    {
        return [
            RepositoryFacadeHookFactory::class,
            PriceFactoryFactory::class,
            SystemConfigFacadeHookFactory::class,
            ChannelRepositoryFacadeHookFactory::class,
        ];
    }

    public function getName(): string
    {
        return self::HOOK_NAME;
    }

    public function getChannelContext(): ChannelContext
    {
        return $this->channelContext;
    }
}
