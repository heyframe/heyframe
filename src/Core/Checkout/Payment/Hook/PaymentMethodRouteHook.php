<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Payment\Hook;

use HeyFrame\Core\Checkout\Payment\PaymentMethodCollection;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Script\Execution\Awareness\ChannelContextAwareTrait;
use HeyFrame\Core\System\Channel\ChannelContext;
use HeyFrame\Core\System\Channel\FrontApiRequestHook;

/**
 * Triggered when PaymentMethodRoute is requested
 *
 * @hook-use-case data_loading
 *
 * @since 6.5.0.0
 *
 * @final
 */
#[Package('checkout')]
class PaymentMethodRouteHook extends FrontApiRequestHook
{
    use ChannelContextAwareTrait;

    final public const HOOK_NAME = 'payment-method-route-request';

    /**
     * @internal
     */
    public function __construct(
        private readonly PaymentMethodCollection $collection,
        private readonly bool $onlyAvailable,
        protected ChannelContext $channelContext,
    ) {
        parent::__construct($channelContext->getContext());
    }

    public function getName(): string
    {
        return self::HOOK_NAME;
    }

    public function getCollection(): PaymentMethodCollection
    {
        return $this->collection;
    }

    public function isOnlyAvailable(): bool
    {
        return $this->onlyAvailable;
    }
}
