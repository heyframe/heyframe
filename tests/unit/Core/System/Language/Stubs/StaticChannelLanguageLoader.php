<?php declare(strict_types=1);

namespace HeyFrame\Tests\Unit\Core\System\Language\Stubs;

use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Language\ChannelLanguageLoader;

/**
 * @internal
 */
#[Package('fundamentals@discovery')]
class StaticChannelLanguageLoader extends ChannelLanguageLoader
{
    /**
     * @param array<string, list<string>> $languages
     */
    public function __construct(private readonly array $languages = [])
    {
    }

    /**
     * {@inheritDoc}
     */
    public function loadLanguages(): array
    {
        return $this->languages;
    }
}
