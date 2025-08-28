<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Gateway\Command;

use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Struct\Collection;

/**
 * @extends Collection<AbstractCheckoutGatewayCommand>
 */
#[Package('checkout')]
class CheckoutGatewayCommandCollection extends Collection
{
}
