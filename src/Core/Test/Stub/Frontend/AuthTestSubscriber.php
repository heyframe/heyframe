<?php declare(strict_types=1);

namespace HeyFrame\Core\Test\Stub\Frontend;

use HeyFrame\Core\Checkout\Customer\Event\CustomerAccountRecoverRequestEvent;
use HeyFrame\Frontend\Event\FrontendRenderEvent;
use HeyFrame\Frontend\Page\Account\RecoverPassword\AccountRecoverPasswordPage;
use HeyFrame\Frontend\Page\Account\RecoverPassword\AccountRecoverPasswordPageLoadedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @internal
 */
class AuthTestSubscriber implements EventSubscriberInterface
{
    public static ?FrontendRenderEvent $renderEvent = null;

    public static ?AccountRecoverPasswordPage $page = null;

    public static ?CustomerAccountRecoverRequestEvent $customerRecoveryEvent = null;

    public static function getSubscribedEvents(): array
    {
        return [
            FrontendRenderEvent::class => 'onRender',
            AccountRecoverPasswordPageLoadedEvent::class => 'onPageLoad',
            CustomerAccountRecoverRequestEvent::EVENT_NAME => 'onRecoverEvent',
        ];
    }

    public function onRecoverEvent(CustomerAccountRecoverRequestEvent $event): void
    {
        self::$customerRecoveryEvent = $event;
    }

    public function onRender(FrontendRenderEvent $event): void
    {
        $skippedViews = [
            '@Frontend/frontend/layout/header.html.twig',
            '@Frontend/frontend/layout/footer.html.twig',
        ];
        if (\in_array($event->getView(), $skippedViews, true)) {
            return;
        }

        self::$renderEvent = $event;
    }

    public function onPageLoad(AccountRecoverPasswordPageLoadedEvent $event): void
    {
        self::$page = $event->getPage();
    }
}
