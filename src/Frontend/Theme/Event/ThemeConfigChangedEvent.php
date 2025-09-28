<?php declare(strict_types=1);

namespace HeyFrame\Frontend\Theme\Event;

use HeyFrame\Core\Framework\Log\Package;
use Symfony\Contracts\EventDispatcher\Event;

#[Package('framework')]
class ThemeConfigChangedEvent extends Event
{
    public function __construct(
        private readonly string $themeId,
        protected array $config
    ) {
    }

    public function getConfig(): array
    {
        return $this->config;
    }

    public function getThemeId(): string
    {
        return $this->themeId;
    }
}
