<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Store\Exception;

use HeyFrame\Core\Framework\HeyFrameHttpException;
use HeyFrame\Core\Framework\Log\Package;

#[Package('checkout')]
class StoreInvalidCredentialsException extends HeyFrameHttpException
{
    public function __construct()
    {
        parent::__construct('Invalid credentials');
    }

    public function getErrorCode(): string
    {
        return 'FRAMEWORK__STORE_INVALID_CREDENTIALS';
    }
}
