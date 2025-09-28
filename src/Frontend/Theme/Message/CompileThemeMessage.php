<?php declare(strict_types=1);

namespace HeyFrame\Frontend\Theme\Message;

use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\MessageQueue\AsyncMessageInterface;

/**
 * @internal
 *
 * used to compile the themes in the queue
 */
#[Package('framework')]
class CompileThemeMessage implements AsyncMessageInterface
{
    public function __construct(
        private readonly string $channelId,
        private readonly string $themeId,
        private readonly bool $withAssets,
        private readonly Context $context
    ) {
    }

    public function getChannelId(): string
    {
        return $this->channelId;
    }

    public function getThemeId(): string
    {
        return $this->themeId;
    }

    public function isWithAssets(): bool
    {
        return $this->withAssets;
    }

    public function getContext(): Context
    {
        return $this->context;
    }
}
