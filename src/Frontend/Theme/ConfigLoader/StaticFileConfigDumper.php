<?php
declare(strict_types=1);

namespace HeyFrame\Frontend\Theme\ConfigLoader;

use League\Flysystem\FilesystemOperator;
use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Frontend\Theme\Event\ThemeAssignedEvent;
use HeyFrame\Frontend\Theme\Event\ThemeConfigChangedEvent;
use HeyFrame\Frontend\Theme\Event\ThemeConfigResetEvent;
use HeyFrame\Frontend\Theme\FrontendPluginConfiguration\FileCollection;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @internal
 */
#[Package('framework')]
class StaticFileConfigDumper implements EventSubscriberInterface
{
    /**
     * @internal
     */
    public function __construct(
        private readonly AbstractConfigLoader $configLoader,
        private readonly AbstractAvailableThemeProvider $availableThemeProvider,
        private readonly FilesystemOperator $privateFilesystem,
        private readonly FilesystemOperator $temporaryFilesystem
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ThemeConfigChangedEvent::class => 'dumpConfigFromEvent',
            ThemeAssignedEvent::class => 'dumpConfigFromEvent',
            ThemeConfigResetEvent::class => 'dumpConfigFromEvent',
        ];
    }

    /**
     * @param array<string, FileCollection|string> $dump
     */
    public function dumpConfigInVar(string $filePath, array $dump): void
    {
        $this->temporaryFilesystem->write($filePath, \json_encode($dump, \JSON_PRETTY_PRINT | \JSON_THROW_ON_ERROR));
    }

    public function dumpConfig(Context $context): void
    {
        $channelToTheme = $this->availableThemeProvider->load($context, false);
        $this->privateFilesystem->write(StaticFileAvailableThemeProvider::THEME_INDEX, \json_encode($channelToTheme, \JSON_THROW_ON_ERROR));

        foreach ($channelToTheme as $themeId) {
            $struct = $this->configLoader->load($themeId, $context);

            $path = \sprintf('theme-config/%s.json', $themeId);

            $this->privateFilesystem->write($path, \json_encode($struct->jsonSerialize(), \JSON_THROW_ON_ERROR));
        }
    }

    public function dumpConfigFromEvent(): void
    {
        $this->dumpConfig(Context::createDefaultContext());
    }
}
