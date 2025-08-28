<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Store\Exception;

use HeyFrame\Core\Framework\HeyFrameHttpException;
use HeyFrame\Core\Framework\Log\Package;
use Symfony\Component\HttpFoundation\Response;

#[Package('checkout')]
class LicenseNotFoundException extends HeyFrameHttpException
{
    public function __construct(
        int $licenseId,
        array $parameters = [],
        ?\Throwable $e = null
    ) {
        $parameters['licenseId'] = $licenseId;

        parent::__construct('Could not find license with id {{licenseId}}', $parameters, $e);
    }

    public function getErrorCode(): string
    {
        return 'FRAMEWORK__LICENSE_NOT_FOUND';
    }

    public function getStatusCode(): int
    {
        return Response::HTTP_NOT_FOUND;
    }
}
