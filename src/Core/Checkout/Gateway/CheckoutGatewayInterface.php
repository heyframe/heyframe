<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Gateway;

use HeyFrame\Core\Checkout\Gateway\Command\Struct\CheckoutGatewayPayloadStruct;
use HeyFrame\Core\Framework\Log\Package;

#[Package('checkout')]
interface CheckoutGatewayInterface
{
    public function process(CheckoutGatewayPayloadStruct $payload): CheckoutGatewayResponse;
}
