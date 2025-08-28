<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Payment\DataAbstractionLayer;

use HeyFrame\Core\Framework\DataAbstractionLayer\Indexing\EntityIndexingMessage;
use HeyFrame\Core\Framework\Log\Package;

#[Package('checkout')]
class PaymentMethodIndexingMessage extends EntityIndexingMessage
{
}
