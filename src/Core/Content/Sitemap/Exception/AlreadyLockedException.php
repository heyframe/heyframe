<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Sitemap\Exception;

use HeyFrame\Core\Framework\HeyFrameHttpException;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelContext;

#[Package('discovery')]
class AlreadyLockedException extends HeyFrameHttpException
{
    public function __construct(ChannelContext $channelContext)
    {
        parent::__construct('Cannot acquire lock for sales channel {{channelId}} and language {{languageId}}', [
            'channelId' => $channelContext->getChannelId(),
            'languageId' => $channelContext->getLanguageId(),
        ]);
    }

    public function getErrorCode(): string
    {
        return 'CONTENT__SITEMAP_ALREADY_LOCKED';
    }
}
