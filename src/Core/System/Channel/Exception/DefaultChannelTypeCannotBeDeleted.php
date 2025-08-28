<?php declare(strict_types=1);

namespace HeyFrame\Core\System\Channel\Exception;

use HeyFrame\Core\Framework\HeyFrameHttpException;
use HeyFrame\Core\Framework\Log\Package;
use Symfony\Component\HttpFoundation\Response;

#[Package('discovery')]
class DefaultChannelTypeCannotBeDeleted extends HeyFrameHttpException
{
    public function __construct(string $id)
    {
        parent::__construct('Cannot delete system default sales channel type', ['id' => $id]);
    }

    public function getErrorCode(): string
    {
        return 'SYSTEM__CHANNEL_DEFAULT_TYPE_CANNOT_BE_DELETED';
    }

    public function getStatusCode(): int
    {
        return Response::HTTP_BAD_REQUEST;
    }
}
