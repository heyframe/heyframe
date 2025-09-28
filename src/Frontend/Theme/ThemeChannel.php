<?php declare(strict_types=1);

namespace HeyFrame\Frontend\Theme;

use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Struct\Struct;

#[Package('framework')]
class ThemeChannel extends Struct
{
    public function __construct(
        protected string $themeId,
        protected string $channelId
    ) {
    }

    public function getThemeId(): string
    {
        return $this->themeId;
    }

    public function setThemeId(string $themeId): void
    {
        $this->themeId = $themeId;
    }

    public function getChannelId(): string
    {
        return $this->channelId;
    }

    public function setChannelId(string $channelId): void
    {
        $this->channelId = $channelId;
    }
}
