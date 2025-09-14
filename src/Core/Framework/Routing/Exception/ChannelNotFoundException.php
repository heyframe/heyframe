<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Routing\Exception;

use HeyFrame\Core\Framework\HeyFrameHttpException;
use HeyFrame\Core\Framework\Log\Package;
use Symfony\Component\HttpFoundation\Response;

#[Package('framework')]
class ChannelNotFoundException extends HeyFrameHttpException
{
    public function __construct()
    {
        parent::__construct('No matching channel found.');
    }

    public function getErrorCode(): string
    {
        return 'FRAMEWORK__ROUTING_CHANNEL_NOT_FOUND';
    }

    public function getStatusCode(): int
    {
        return Response::HTTP_PRECONDITION_FAILED;
    }
}
