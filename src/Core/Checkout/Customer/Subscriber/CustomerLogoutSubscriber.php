<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Customer\Subscriber;

use HeyFrame\Core\Checkout\Customer\Event\CustomerLogoutEvent;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\PlatformRequest;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * @internal
 */
#[Package('checkout')]
class CustomerLogoutSubscriber implements EventSubscriberInterface
{
    /**
     * @internal
     */
    public function __construct(private readonly RequestStack $requestStack)
    {
    }

    /**
     * @return array<string, string|array{0: string, 1: int}|list<array{0: string, 1?: int}>>
     */
    public static function getSubscribedEvents(): array
    {
        return [
            CustomerLogoutEvent::class => ['onCustomerLogout', -10000],
        ];
    }

    public function onCustomerLogout(CustomerLogoutEvent $event): void
    {
        $event->getChannelContext()->setImitatingUserId(null);

        $mainRequest = $this->requestStack->getMainRequest();

        if (!$mainRequest?->hasSession()) {
            return;
        }

        $mainRequest->getSession()->remove(PlatformRequest::ATTRIBUTE_IMITATING_USER_ID);
    }
}
