<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Api\Context\Exception;

use HeyFrame\Core\Framework\HeyFrameHttpException;
use HeyFrame\Core\Framework\Log\Package;

/**
 * @deprecated tag:v6.8.0 - reason:remove-exception - Will be removed in v6.8.0.0. Use `\HeyFrame\Core\Framework\Store\StoreException::invalidContextSourceUser` instead.
 */
#[Package('framework')]
class InvalidContextSourceUserException extends HeyFrameHttpException
{
    public function __construct(string $contextSource)
    {
        parent::__construct(
            '{{ contextSource }} does not have a valid user ID',
            ['contextSource' => $contextSource]
        );
    }

    public function getErrorCode(): string
    {
        return 'FRAMEWORK__INVALID_CONTEXT_SOURCE_USER';
    }
}
