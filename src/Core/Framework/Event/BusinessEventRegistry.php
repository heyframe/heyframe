<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Event;

use HeyFrame\Core\Checkout\Cart\Event\CheckoutOrderPlacedEvent;
use HeyFrame\Core\Checkout\Customer\Event\CustomerAccountRecoverRequestEvent;
use HeyFrame\Core\Checkout\Customer\Event\CustomerBeforeLoginEvent;
use HeyFrame\Core\Checkout\Customer\Event\CustomerDeletedEvent;
use HeyFrame\Core\Checkout\Customer\Event\CustomerDoubleOptInRegistrationEvent;
use HeyFrame\Core\Checkout\Customer\Event\CustomerGroupRegistrationAccepted;
use HeyFrame\Core\Checkout\Customer\Event\CustomerGroupRegistrationDeclined;
use HeyFrame\Core\Checkout\Customer\Event\CustomerLoginEvent;
use HeyFrame\Core\Checkout\Customer\Event\CustomerLogoutEvent;
use HeyFrame\Core\Checkout\Customer\Event\CustomerRegisterEvent;
use HeyFrame\Core\Checkout\Customer\Event\DoubleOptInGuestOrderEvent;
use HeyFrame\Core\Checkout\Customer\Event\GuestCustomerRegisterEvent;
use HeyFrame\Core\Checkout\Order\Event\OrderPaymentMethodChangedEvent;
use HeyFrame\Core\Content\ContactForm\Event\ContactFormEvent;
use HeyFrame\Core\Content\MailTemplate\Service\Event\MailBeforeSentEvent;
use HeyFrame\Core\Content\MailTemplate\Service\Event\MailBeforeValidateEvent;
use HeyFrame\Core\Content\MailTemplate\Service\Event\MailSentEvent;
use HeyFrame\Core\Content\Newsletter\Event\NewsletterConfirmEvent;
use HeyFrame\Core\Content\Newsletter\Event\NewsletterRegisterEvent;
use HeyFrame\Core\Content\Newsletter\Event\NewsletterUnsubscribeEvent;
use HeyFrame\Core\Content\Product\Channel\Review\Event\ReviewFormEvent;
use HeyFrame\Core\Content\ProductExport\Event\ProductExportLoggingEvent;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\User\Recovery\UserRecoveryRequestEvent;

#[Package('fundamentals@after-sales')]
class BusinessEventRegistry
{
    /**
     * @var list<class-string>
     */
    private array $classes = [
        CustomerBeforeLoginEvent::class,
        CustomerLoginEvent::class,
        CustomerLogoutEvent::class,
        CustomerDeletedEvent::class,
        UserRecoveryRequestEvent::class,
        CheckoutOrderPlacedEvent::class,
        OrderPaymentMethodChangedEvent::class,
        CustomerAccountRecoverRequestEvent::class,
        CustomerDoubleOptInRegistrationEvent::class,
        CustomerGroupRegistrationAccepted::class,
        CustomerGroupRegistrationDeclined::class,
        CustomerRegisterEvent::class,
        DoubleOptInGuestOrderEvent::class,
        GuestCustomerRegisterEvent::class,
        ContactFormEvent::class,
        ReviewFormEvent::class,
        MailBeforeSentEvent::class,
        MailBeforeValidateEvent::class,
        MailSentEvent::class,
        NewsletterConfirmEvent::class,
        NewsletterRegisterEvent::class,
        NewsletterUnsubscribeEvent::class,
        ProductExportLoggingEvent::class,
    ];

    /**
     * @param list<class-string> $classes
     */
    public function addClasses(array $classes): void
    {
        /** @var list<class-string> */
        $classes = array_unique(array_merge($this->classes, $classes));

        $this->classes = $classes;
    }

    /**
     * @return list<class-string>
     */
    public function getClasses(): array
    {
        return $this->classes;
    }
}
