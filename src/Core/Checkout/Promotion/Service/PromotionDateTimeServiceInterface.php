<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Promotion\Service;

use HeyFrame\Core\Framework\Log\Package;

#[Package('checkout')]
interface PromotionDateTimeServiceInterface
{
    public function getNow(): string;
}
