<?php declare(strict_types=1);

namespace HeyFrame\Frontend\Theme\Event;

use HeyFrame\Core\Framework\Log\Package;
use Symfony\Contracts\EventDispatcher\Event;

#[Package('framework')]
class ThemeAssignedEvent extends Event
{
    public function __construct(
        private readonly string $themeId,
        private readonly string $channelId
    ) {
    }

    public function getThemeId(): string
    {
        return $this->themeId;
    }

    public function getChannelId(): string
    {
        return $this->channelId;
    }
}
