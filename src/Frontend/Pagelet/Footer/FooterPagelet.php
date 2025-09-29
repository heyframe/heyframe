<?php declare(strict_types=1);

namespace HeyFrame\Frontend\Pagelet\Footer;

use HeyFrame\Core\Checkout\Payment\PaymentMethodCollection;
use HeyFrame\Core\Content\Navigation\NavigationCollection;
use HeyFrame\Core\Content\Navigation\Tree\Tree;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Frontend\Pagelet\NavigationPagelet;

/**
 * @codeCoverageIgnore
 */
#[Package('framework')]
class FooterPagelet extends NavigationPagelet
{
    public function __construct(
        ?Tree $navigation,
        protected NavigationCollection $serviceMenu,
        protected PaymentMethodCollection $paymentMethods,
    ) {
        parent::__construct($navigation);
    }

    public function getServiceMenu(): NavigationCollection
    {
        return $this->serviceMenu;
    }

    public function setServiceMenu(NavigationCollection $serviceMenu): void
    {
        $this->serviceMenu = $serviceMenu;
    }

    public function getPaymentMethods(): PaymentMethodCollection
    {
        return $this->paymentMethods;
    }

    public function setPaymentMethods(PaymentMethodCollection $paymentMethods): void
    {
        $this->paymentMethods = $paymentMethods;
    }
}
