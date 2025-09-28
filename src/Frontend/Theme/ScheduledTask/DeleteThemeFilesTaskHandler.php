<?php

declare(strict_types=1);

namespace HeyFrame\Frontend\Theme\ScheduledTask;

use Doctrine\DBAL\Connection;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityRepository;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\MessageQueue\ScheduledTask\ScheduledTaskHandler;
use HeyFrame\Frontend\Theme\AbstractThemePathBuilder;
use League\Flysystem\FilesystemOperator;
use League\Flysystem\FilesystemReader;
use League\Flysystem\StorageAttributes;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

/**
 * @internal
 */
#[AsMessageHandler(handles: DeleteThemeFilesTask::class)]
#[Package('framework')]
final class DeleteThemeFilesTaskHandler extends ScheduledTaskHandler
{
    public function __construct(
        EntityRepository $scheduledTaskRepository,
        LoggerInterface $exceptionLogger,
        private readonly Connection $connection,
        private readonly FilesystemOperator $themeFileSystem,
        private readonly AbstractThemePathBuilder $themePathBuilder,
    ) {
        parent::__construct($scheduledTaskRepository, $exceptionLogger);
    }

    public function run(): void
    {
        $usedThemePaths = $this->getUsedThemePaths();

        $themeDirectories = $this->themeFileSystem->listContents('theme')->filter(function (StorageAttributes $themeDirectory) use ($usedThemePaths) {
            // Only delete unused theme directories
            if (\in_array($themeDirectory->path(), $usedThemePaths, true)) {
                return false;
            }

            // Find the first file in the directory, as on some file systems the directories are only virtual and do not have a timestamp
            $modifiedTimestampOfFirstFile = $this->getModifiedTimestampOfFirstFile($themeDirectory);

            // If no files are found in the directory, delete it
            if ($modifiedTimestampOfFirstFile === null) {
                return true;
            }

            // Only delete directories that were last modified more than 24 hours ago, as more recently compiled themes might still be referenced in cached responses
            $twentyFourHoursAgo = (new \DateTimeImmutable())->modify('-24 hours')->getTimestamp();

            return $twentyFourHoursAgo > $modifiedTimestampOfFirstFile;
        });

        foreach ($themeDirectories as $themeDirectory) {
            $themePath = $themeDirectory->path();
            $this->themeFileSystem->deleteDirectory($themePath);
        }
    }

    /**
     * @return list<string>
     */
    private function getUsedThemePaths(): array
    {
        $channelThemeMappings = $this->connection->fetchAllAssociative(
            'SELECT LOWER(HEX(channel_id)) AS channelId, LOWER(HEX(theme_id)) AS themeId
             FROM theme_channel'
        );

        $themePaths = [];
        foreach (array_unique(array_column($channelThemeMappings, 'themeId')) as $themeId) {
            // Add path with themeId (for assets)
            $themePaths[] = 'theme' . \DIRECTORY_SEPARATOR . $themeId;
        }

        foreach ($channelThemeMappings as $channelThemeMapping) {
            // Add path with themePrefix (for CSS and JS files)
            $themePaths[] = 'theme' . \DIRECTORY_SEPARATOR . $this->themePathBuilder->assemblePath($channelThemeMapping['channelId'], $channelThemeMapping['themeId']);
        }

        return $themePaths;
    }

    private function getModifiedTimestampOfFirstFile(StorageAttributes $themeDirectory): ?int
    {
        foreach ($this->themeFileSystem->listContents($themeDirectory->path(), FilesystemReader::LIST_DEEP) as $file) {
            if (!$file->isFile()) {
                continue;
            }

            $lastModified = $file->lastModified();
            if ($lastModified === null) {
                continue;
            }

            return $lastModified;
        }

        return null;
    }
}
