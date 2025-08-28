<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Cart\Hook;

use HeyFrame\Core\Checkout\Cart\Cart;
use HeyFrame\Core\Checkout\Cart\Facade\CartFacadeHookFactory;
use HeyFrame\Core\Checkout\Cart\Facade\PriceFactoryFactory;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Script\Execution\Hook;
use HeyFrame\Core\System\Channel\ChannelContext;
use HeyFrame\Core\System\SystemConfig\Facade\SystemConfigFacadeHookFactory;

/**
 * Triggered during the cart calculation process.
 *
 * @hook-use-case cart_manipulation
 *
 * @since 6.4.8.0
 *
 * @final
 */
#[Package('checkout')]
class CartHook extends Hook implements CartAware
{
    final public const HOOK_NAME = 'cart';

    private readonly ChannelContext $channelContext;

    /**
     * @internal
     */
    public function __construct(
        private readonly Cart $cart,
        ChannelContext $context
    ) {
        parent::__construct($context->getContext());
        $this->channelContext = $context;
    }

    public function getCart(): Cart
    {
        return $this->cart;
    }

    public static function getServiceIds(): array
    {
        return [
            CartFacadeHookFactory::class,
            PriceFactoryFactory::class,
            SystemConfigFacadeHookFactory::class,
        ];
    }

    public function getName(): string
    {
        if ($this->cart->getSource()) {
            return self::HOOK_NAME . '-' . $this->cart->getSource();
        }

        return self::HOOK_NAME;
    }

    public function getChannelContext(): ChannelContext
    {
        return $this->channelContext;
    }
}
