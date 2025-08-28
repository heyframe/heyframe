<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Store\Exception;

use HeyFrame\Core\Framework\HeyFrameHttpException;
use HeyFrame\Core\Framework\Log\Package;

#[Package('checkout')]
class StoreLicenseDomainMissingException extends HeyFrameHttpException
{
    public function __construct()
    {
        parent::__construct('Store license domain is missing');
    }

    public function getErrorCode(): string
    {
        return 'FRAMEWORK__STORE_LICENSE_DOMAIN_IS_MISSING';
    }
}
