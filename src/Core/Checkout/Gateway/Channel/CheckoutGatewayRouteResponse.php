<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Gateway\Channel;

use HeyFrame\Core\Checkout\Cart\Error\ErrorCollection;
use HeyFrame\Core\Checkout\Payment\PaymentMethodCollection;
use HeyFrame\Core\Checkout\Shipping\ShippingMethodCollection;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Struct\ArrayStruct;
use HeyFrame\Core\System\Channel\StoreApiResponse;

/**
 * @extends StoreApiResponse<ArrayStruct<array{payments: PaymentMethodCollection, shipments: ShippingMethodCollection, errors: ErrorCollection}>>
 */
#[Package('checkout')]
class CheckoutGatewayRouteResponse extends StoreApiResponse
{
    public function __construct(
        private PaymentMethodCollection $payments,
        private ShippingMethodCollection $shipments,
        private ErrorCollection $errors,
    ) {
        parent::__construct(new ArrayStruct([
            'payments' => $payments,
            'shipments' => $shipments,
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

    public function getShippingMethods(): ShippingMethodCollection
    {
        return $this->shipments;
    }

    public function setShippingMethods(ShippingMethodCollection $shipments): void
    {
        $this->shipments = $shipments;
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
