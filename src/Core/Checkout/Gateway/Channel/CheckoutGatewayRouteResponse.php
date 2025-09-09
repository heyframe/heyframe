<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Gateway\Channel;

use HeyFrame\Core\Checkout\Cart\Error\ErrorCollection;
use HeyFrame\Core\Checkout\Payment\PaymentMethodCollection;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Struct\ArrayStruct;
use HeyFrame\Core\System\Channel\FrontApiResponse;

/**
 * @extends FrontApiResponse<ArrayStruct<array{payments: PaymentMethodCollection, errors: ErrorCollection}>>
 */
#[Package('checkout')]
class CheckoutGatewayRouteResponse extends FrontApiResponse
{
    public function __construct(
        private PaymentMethodCollection $payments,
        private ErrorCollection $errors,
    ) {
        parent::__construct(new ArrayStruct([
            'payments' => $payments,
            'errors' => $errors,
        ]));
    }

    public function getPaymentMethods(): PaymentMethodCollection
    {
        return $this->payments;
    }

    public function setPaymentMethods(PaymentMethodCollection $payments): void
    {
        $this->payments = $payments;
    }

    public function getErrors(): ErrorCollection
    {
        return $this->errors;
    }

    public function setErrors(ErrorCollection $errors): void
    {
        $this->errors = $errors;
    }
}
