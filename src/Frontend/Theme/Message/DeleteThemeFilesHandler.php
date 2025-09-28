<?php declare(strict_types=1);

namespace HeyFrame\Frontend\Theme\Message;

use HeyFrame\Core\Framework\Feature;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Frontend\Theme\AbstractThemePathBuilder;
use League\Flysystem\FilesystemOperator;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

/**
 * @internal
 *
 * @deprecated tag:v6.8.0 - Will be removed. Unused theme files are now deleted with a scheduled task.
 * @see \HeyFrame\Frontend\Theme\ScheduledTask\DeleteThemeFilesTask
 * @see \HeyFrame\Frontend\Theme\ScheduledTask\DeleteThemeFilesTaskHandler
 */
#[AsMessageHandler]
#[Package('framework')]
final readonly class DeleteThemeFilesHandler
{
    public function __construct(
        private FilesystemOperator $filesystem,
        private AbstractThemePathBuilder $pathBuilder,
    ) {
    }

    public function __invoke(DeleteThemeFilesMessage $message): void
    {
        Feature::triggerDeprecationOrThrow(
            'v6.8.0.0',
            Feature::deprecatedMethodMessage(self::class, __METHOD__, 'v6.8.0.0')
        );

        $currentPath = $this->pathBuilder->assemblePath($message->getChannelId(), $message->getThemeId());
        if ($currentPath === $message->getThemePath()) {
            return;
        }

        $this->filesystem->deleteDirectory('theme' . \DIRECTORY_SEPARATOR . $message->getThemePath());
    }
}
