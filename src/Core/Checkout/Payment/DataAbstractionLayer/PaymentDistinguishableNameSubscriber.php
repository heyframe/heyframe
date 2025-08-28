<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Payment\DataAbstractionLayer;

use HeyFrame\Core\Checkout\Payment\PaymentEvents;
use HeyFrame\Core\Checkout\Payment\PaymentMethodEntity;
use HeyFrame\Core\Framework\DataAbstractionLayer\Event\EntityLoadedEvent;
use HeyFrame\Core\Framework\Log\Package;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @internal
 */
#[Package('checkout')]
class PaymentDistinguishableNameSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            PaymentEvents::PAYMENT_METHOD_LOADED_EVENT => 'addDistinguishablePaymentName',
        ];
    }

    /**
     * @param EntityLoadedEvent<PaymentMethodEntity> $event
     */
    public function addDistinguishablePaymentName(EntityLoadedEvent $event): void
    {
        foreach ($event->getEntities() as $payment) {
            if ($payment->getTranslation('distinguishableName') === null) {
                $payment->addTranslated('distinguishableName', $payment->getTranslation('name'));
            }
            if ($payment->getDistinguishableName() === null) {
                $payment->setDistinguishableName($payment->getName());
            }
        }
    }
}
