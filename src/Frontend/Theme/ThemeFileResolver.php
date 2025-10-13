<?php declare(strict_types=1);

namespace HeyFrame\Frontend\Theme;

use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Frontend\Framework\Twig\Components\TwigComponent;
use HeyFrame\Frontend\Framework\Twig\Components\TwigComponentHelper;
use HeyFrame\Frontend\Theme\Exception\ThemeCompileException;
use HeyFrame\Frontend\Theme\Exception\ThemeException;
use HeyFrame\Frontend\Theme\FrontendPluginConfiguration\File;
use HeyFrame\Frontend\Theme\FrontendPluginConfiguration\FileCollection;
use HeyFrame\Frontend\Theme\FrontendPluginConfiguration\FrontendPluginConfiguration;
use HeyFrame\Frontend\Theme\FrontendPluginConfiguration\FrontendPluginConfigurationCollection;

#[Package('framework')]
class ThemeFileResolver
{
    final public const SCRIPT_FILES = 'script';
    final public const STYLE_FILES = 'style';

    /**
     * @internal
     */
    public function __construct(
        private readonly ThemeFilesystemResolver $themeFilesystemResolver,
        private readonly TwigComponentHelper $twigComponentHelper
    ) {
    }

    /**
     * @return array<string, FileCollection>
     */
    public function resolveFiles(
        FrontendPluginConfiguration $themeConfig,
        FrontendPluginConfigurationCollection $configurationCollection,
        bool $onlySourceFiles
    ): array {
        return [
            self::SCRIPT_FILES => $this->resolveScriptFiles(
                $themeConfig,
                $configurationCollection,
                $onlySourceFiles
            ),
            self::STYLE_FILES => $this->resolveStyleFiles(
                $themeConfig,
                $configurationCollection,
                $onlySourceFiles
            ),
        ];
    }

    public function resolveScriptFiles(
        FrontendPluginConfiguration $themeConfig,
        FrontendPluginConfigurationCollection $configurationCollection,
        bool $onlySourceFiles
    ): FileCollection {
        return $this->resolve(
            self::SCRIPT_FILES,
            $themeConfig,
            $configurationCollection,
            $onlySourceFiles,
            $this->collectConfigurationScriptFiles(...)
        );
    }

    public function resolveStyleFiles(
        FrontendPluginConfiguration $themeConfig,
        FrontendPluginConfigurationCollection $configurationCollection,
        bool $onlySourceFiles
    ): FileCollection {
        return $this->resolve(
            self::STYLE_FILES,
            $themeConfig,
            $configurationCollection,
            $onlySourceFiles,
            fn (FrontendPluginConfiguration $configuration) => $configuration->getStyleFiles()
        );
    }

    private function collectConfigurationScriptFiles(FrontendPluginConfiguration $configuration, bool $onlySourceFiles): FileCollection
    {
        $fileCollection = new FileCollection();
        $scriptFiles = $configuration->getScriptFiles();
        $addSourceFile = $configuration->getFrontendEntryFilepath() && $onlySourceFiles;

        // add source file at the beginning if no other theme is included first
        if ($addSourceFile
            && $configuration->getFrontendEntryFilepath()
            && ($scriptFiles->count() === 0 || !$scriptFiles->first() || !$this->isInclude($scriptFiles->first()->getFilepath()))
        ) {
            $fileCollection->add(new File($configuration->getFrontendEntryFilepath()));
        }
        foreach ($scriptFiles as $scriptFile) {
            if ($onlySourceFiles && !$this->isInclude($scriptFile->getFilepath())) {
                continue;
            }
            $fileCollection->add($scriptFile);
        }
        if ($addSourceFile
            && $configuration->getFrontendEntryFilepath()
            && $scriptFiles->count() > 0
            && $scriptFiles->first()
            && $this->isInclude($scriptFiles->first()->getFilepath())
        ) {
            $fileCollection->add(new File($configuration->getFrontendEntryFilepath()));
        }

        foreach ($fileCollection as $file) {
            $file->assetName = $configuration->getAssetName();
        }

        return $fileCollection;
    }

    /**
     * Resolves theme files by processing both direct file paths and namespaced imports
     *
     * @param FrontendPluginConfiguration $themeConfig The theme configuration to resolve files for
     * @param FrontendPluginConfigurationCollection $configurationCollection Collection of all available theme configurations
     * @param bool $onlySourceFiles Whether to only include source files (true) or also compiled files (false)
     * @param callable(FrontendPluginConfiguration, bool): FileCollection $configFileResolver Function to get the initial file collection (either style or script files)
     * @param array<int, string> $included List of already included files to prevent duplicates
     *
     * @return FileCollection Collection of resolved files
     */
    private function resolve(
        string $fileType,
        FrontendPluginConfiguration $themeConfig,
        FrontendPluginConfigurationCollection $configurationCollection,
        bool $onlySourceFiles,
        callable $configFileResolver,
        array $included = []
    ): FileCollection {
        // convertPathsToAbsolute changes the path, this should not affect the passed configuration
        $themeConfig = clone $themeConfig;

        // Get initial file collection using the provided resolver
        $files = $configFileResolver($themeConfig, $onlySourceFiles);

        // Return empty collection if no files found
        if ($files->count() === 0) {
            return $files;
        }

        // Convert all relative paths to absolute paths
        $this->convertPathsToAbsolute($themeConfig, $files);

        // Initialize collection for resolved files
        $resolvedFiles = new FileCollection();
        $nextIncluded = $included;

        // First pass: collect all namespaced imports (@) to track what needs to be included
        foreach ($files as $file) {
            $filepath = $file->getFilepath();
            if ($this->isInclude($filepath)) {
                $nextIncluded[] = $filepath;
            }
        }

        // Second pass: process each file
        foreach ($files as $file) {
            $filepath = $file->getFilepath();

            // Handle direct file paths (not starting with @)
            if (!$this->isInclude($filepath)) {
                if (\is_file($filepath)) {
                    $resolvedFiles->add($file);

                    continue;
                }
                // removes file with old js structure (before async changes) from collection
                if (!str_ends_with($filepath, $file->assetName . '/' . basename($filepath))) {
                    continue;
                }

                throw new ThemeCompileException(
                    $themeConfig->getTechnicalName(),
                    \sprintf('Unable to load file "Resources/%s". Did you forget to build the theme? Try running ./bin/build-frontend.sh', $filepath)
                );
            }

            // Skip if this namespace was already included to prevent duplicates
            if (\in_array($filepath, $included, true)) {
                continue;
            }
            $included[] = $filepath;

            // Handle @Plugins namespace - include all non-theme plugins
            if ($filepath === '@Plugins') {
                foreach ($configurationCollection->getNoneThemes() as $plugin) {
                    foreach ($this->resolve(
                        $fileType,
                        $plugin,
                        $configurationCollection,
                        $onlySourceFiles,
                        $configFileResolver,
                        $nextIncluded
                    ) as $item) {
                        $resolvedFiles->add($item);
                    }
                }
                continue;
            }

            // Handle @Components namespace - include all Twig UX components
            if ($filepath === '@Components') {
                foreach ($this->twigComponentHelper->getComponents() as $component) {
                    /** @var TwigComponent $component */
                    if ($fileType === self::SCRIPT_FILES) {
                        /** @var string|null $componentPath */
                        $componentPath = $component->getScriptPath();
                    } else {
                        /** @var string|null $componentPath */
                        $componentPath = $component->getStylePath();
                    }

                    if ($componentPath !== null) {
                        $namespaceDir = $component->getRelativeNamespaceDirectory();
                        // Use null for assetName if the namespace directory is empty to avoid double slashes
                        $assetName = $namespaceDir !== '' ? $namespaceDir : null;
                        $resolvedFiles->add(new File($componentPath, [], $assetName));
                    }
                }

                continue;
            }

            // Handle @FrontendBootstrap namespace - include base SCSS file
            if ($filepath === '@FrontendBootstrap') {
                $resolvedFiles->add(new File(
                    __DIR__ . '/../Resources/app/frontend/src/scss/base.scss',
                    ['vendor' => __DIR__ . '/../Resources/app/frontend/vendor']
                ));

                continue;
            }

            // Handle other @ namespaces - resolve to specific theme/plugin
            $name = mb_substr($filepath, 1);
            $configuration = $configurationCollection->getByTechnicalName($name);
            if (!$configuration) {
                throw ThemeException::couldNotFindThemeByName($name);
            }
            foreach ($this->resolve($fileType, $configuration, $configurationCollection, $onlySourceFiles, $configFileResolver, $nextIncluded) as $item) {
                $resolvedFiles->add($item);
            }
        }

        return $resolvedFiles;
    }

    private function isInclude(string $file): bool
    {
        return str_starts_with($file, '@');
    }

    private function convertPathsToAbsolute(FrontendPluginConfiguration $themeConfig, FileCollection $files): void
    {
        foreach ($files->getElements() as $file) {
            if ($this->isInclude($file->getFilepath())) {
                continue;
            }

            $fs = $this->themeFilesystemResolver->getFilesystemForFrontendConfig($themeConfig);
            if ($fs->has('Resources', $file->getFilepath())) {
                $file->setFilepath($fs->realpath('Resources', $file->getFilepath()));
            }

            $mapping = $file->getResolveMapping();

            foreach ($mapping as $key => $val) {
                if ($fs->has('Resources', $val)) {
                    $mapping[$key] = $fs->realpath('Resources', $val);
                }
            }

            $file->setResolveMapping($mapping);
        }
    }
}
