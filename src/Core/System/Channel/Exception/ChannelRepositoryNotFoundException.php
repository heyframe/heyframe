<?php declare(strict_types=1);

namespace HeyFrame\Core\System\Channel\Exception;

use HeyFrame\Core\Framework\HeyFrameHttpException;
use HeyFrame\Core\Framework\Log\Package;

#[Package('discovery')]
class ChannelRepositoryNotFoundException extends HeyFrameHttpException
{
    public function __construct(string $entity)
    {
        parent::__construct(
            'ChannelRepository for entity "{{ entityName }}" does not exist.',
            ['entityName' => $entity]
        );
    }

    public function getErrorCode(): string
    {
        return 'FRAMEWORK__CHANNEL_REPOSITORY_NOT_FOUND';
    }
}
