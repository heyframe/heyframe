<?php declare(strict_types=1);

namespace HeyFrame\Frontend\Event;

use HeyFrame\Core\Framework\Log\Package;
use Symfony\Contracts\EventDispatcher\Event;

#[Package('framework')]
class ThemeCompilerConcatenatedStylesEvent extends Event
{
    public function __construct(
        private string $concatenatedStyles,
        private readonly string $channelId
    ) {
    }

    public function getConcatenatedStyles(): string
    {
        return $this->concatenatedStyles;
    }

    public function setConcatenatedStyles(string $concatenatedStyles): void
    {
        $this->concatenatedStyles = $concatenatedStyles;
    }

    public function getChannelId(): string
    {
        return $this->channelId;
    }
}
