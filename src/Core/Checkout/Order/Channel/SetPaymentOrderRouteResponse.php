<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Order\Channel;

use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\SuccessResponse;

#[Package('checkout')]
class SetPaymentOrderRouteResponse extends SuccessResponse
{
}
