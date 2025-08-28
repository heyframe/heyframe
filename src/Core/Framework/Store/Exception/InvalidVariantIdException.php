<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Store\Exception;

use HeyFrame\Core\Framework\HeyFrameHttpException;
use HeyFrame\Core\Framework\Log\Package;
use Symfony\Component\HttpFoundation\Response;

#[Package('checkout')]
class InvalidVariantIdException extends HeyFrameHttpException
{
    public function __construct(
        array $parameters = [],
        ?\Throwable $e = null
    ) {
        parent::__construct('The variant id must be an non empty numeric value.', $parameters, $e);
    }

    public function getErrorCode(): string
    {
        return 'FRAMEWORK__INVALID_VARIANT_ID';
    }

    public function getStatusCode(): int
    {
        return Response::HTTP_BAD_REQUEST;
    }
}
