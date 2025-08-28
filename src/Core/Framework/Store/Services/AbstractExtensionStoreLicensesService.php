<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Store\Services;

use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Store\Struct\ReviewStruct;

/**
 * @internal
 */
#[Package('checkout')]
abstract class AbstractExtensionStoreLicensesService
{
    abstract public function cancelSubscription(int $licenseId, Context $context): void;

    abstract public function rateLicensedExtension(ReviewStruct $rating, Context $context): void;

    abstract protected function getDecorated(): AbstractExtensionStoreLicensesService;
}
