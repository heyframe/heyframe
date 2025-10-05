<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Promotion\Exception;

use HeyFrame\Core\Checkout\Promotion\PromotionException;
use HeyFrame\Core\Framework\Log\Package;

#[Package('checkout')]
class InvalidCodePatternException extends PromotionException
{
}
