<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\ImportExport\Exception;

use HeyFrame\Core\Framework\HeyFrameHttpException;
use HeyFrame\Core\Framework\Log\Package;
use Symfony\Component\HttpFoundation\Response;

#[Package('fundamentals@after-sales')]
class ProfileNotFoundException extends HeyFrameHttpException
{
    public function __construct(string $profileId)
    {
        parent::__construct('Cannot find import/export profile with id {{ profileId }}', ['profileId' => $profileId]);
    }

    public function getStatusCode(): int
    {
        return Response::HTTP_NOT_FOUND;
    }

    public function getErrorCode(): string
    {
        return 'CONTENT__IMPORT_EXPORT_PROFILE_NOT_FOUND';
    }
}
