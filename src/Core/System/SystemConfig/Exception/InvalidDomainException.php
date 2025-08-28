<?php declare(strict_types=1);

namespace HeyFrame\Core\System\SystemConfig\Exception;

use HeyFrame\Core\Framework\HeyFrameHttpException;
use HeyFrame\Core\Framework\Log\Package;
use Symfony\Component\HttpFoundation\Response;

#[Package('framework')]
class InvalidDomainException extends HeyFrameHttpException
{
    public function __construct(string $domain)
    {
        parent::__construct('Invalid domain \'{{ domain }}\'', ['domain' => $domain]);
    }

    public function getStatusCode(): int
    {
        return Response::HTTP_BAD_REQUEST;
    }

    public function getErrorCode(): string
    {
        return 'SYSTEM__INVALID_DOMAIN';
    }
}
