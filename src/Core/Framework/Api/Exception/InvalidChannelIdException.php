<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Api\Exception;

use HeyFrame\Core\Framework\HeyFrameHttpException;
use HeyFrame\Core\Framework\Log\Package;
use Symfony\Component\HttpFoundation\Response;

#[Package('framework')]
class InvalidChannelIdException extends HeyFrameHttpException
{
    public function __construct(string $channelId)
    {
        parent::__construct(
            'The provided channelId "{{ channelId }}" is invalid.',
            ['channelId' => $channelId]
        );
    }

    public function getErrorCode(): string
    {
        return 'FRAMEWORK__INVALID_CHANNEL';
    }

    public function getStatusCode(): int
    {
        return Response::HTTP_BAD_REQUEST;
    }
}
