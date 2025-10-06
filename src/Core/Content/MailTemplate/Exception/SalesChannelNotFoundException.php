<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\MailTemplate\Exception;

use HeyFrame\Core\Framework\Feature;
use HeyFrame\Core\Framework\HeyFrameHttpException;
use HeyFrame\Core\Framework\Log\Package;
use Symfony\Component\HttpFoundation\Response;

/**
 * @deprecated tag:v6.8.0 - Will be removed as it is not used anymore
 */
#[Package('after-sales')]
class SalesChannelNotFoundException extends HeyFrameHttpException
{
    public function __construct(string $channelId)
    {
        parent::__construct(
            'Sales channel with id "{{ channelId }}" was not found.',
            ['channelId' => $channelId]
        );
    }

    public function getErrorCode(): string
    {
        Feature::triggerDeprecationOrThrow(
            'v6.8.0.0',
            Feature::deprecatedClassMessage(self::class, 'v6.8.0.0'),
        );

        return 'CONTENT__SALES_CHANNEL_NOT_FOUND';
    }

    public function getStatusCode(): int
    {
        Feature::triggerDeprecationOrThrow(
            'v6.8.0.0',
            Feature::deprecatedClassMessage(self::class, 'v6.8.0.0'),
        );

        return Response::HTTP_BAD_REQUEST;
    }
}
