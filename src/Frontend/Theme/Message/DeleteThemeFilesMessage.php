<?php declare(strict_types=1);

namespace HeyFrame\Frontend\Theme\Message;

use HeyFrame\Core\Framework\Feature;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\MessageQueue\AsyncMessageInterface;

/**
 * used to delay the deletion of theme files
 *
 * @deprecated tag:v6.8.0 - Will be removed. Unused theme files are now deleted with a scheduled task.
 * @see \HeyFrame\Frontend\Theme\ScheduledTask\DeleteThemeFilesTask
 * @see \HeyFrame\Frontend\Theme\ScheduledTask\DeleteThemeFilesTaskHandler
 */
#[Package('framework')]
class DeleteThemeFilesMessage implements AsyncMessageInterface
{
    public function __construct(
        private readonly string $themePath,
        private readonly string $channelId,
        private readonly string $themeId
    ) {
    }

    public function getThemePath(): string
    {
        Feature::triggerDeprecationOrThrow(
            'v6.8.0.0',
            Feature::deprecatedMethodMessage(self::class, __METHOD__, 'v6.8.0.0')
        );

        return $this->themePath;
    }

    public function getChannelId(): string
    {
        Feature::triggerDeprecationOrThrow(
            'v6.8.0.0',
            Feature::deprecatedMethodMessage(self::class, __METHOD__, 'v6.8.0.0')
        );

        return $this->channelId;
    }

    public function getThemeId(): string
    {
        Feature::triggerDeprecationOrThrow(
            'v6.8.0.0',
            Feature::deprecatedMethodMessage(self::class, __METHOD__, 'v6.8.0.0')
        );

        return $this->themeId;
    }
}
