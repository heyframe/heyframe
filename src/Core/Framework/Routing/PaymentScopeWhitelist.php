<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Routing;

use HeyFrame\Core\Checkout\Payment\Controller\PaymentController;
use HeyFrame\Core\Framework\Log\Package;

#[Package('framework')]
class PaymentScopeWhitelist implements RouteScopeWhitelistInterface
{
    public function applies(string $controllerClass): bool
    {
        return $controllerClass === PaymentController::class;
    }
}
