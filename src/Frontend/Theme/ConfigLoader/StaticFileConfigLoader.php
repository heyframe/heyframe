<?php
declare(strict_types=1);

namespace HeyFrame\Frontend\Theme\ConfigLoader;

use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Plugin\Exception\DecorationPatternException;
use HeyFrame\Frontend\Theme\FrontendPluginConfiguration\File;
use HeyFrame\Frontend\Theme\FrontendPluginConfiguration\FileCollection;
use HeyFrame\Frontend\Theme\FrontendPluginConfiguration\FrontendPluginConfiguration;
use League\Flysystem\FilesystemOperator;

#[Package('framework')]
class StaticFileConfigLoader extends AbstractConfigLoader
{
    /**
     * @internal
     */
    public function __construct(private readonly FilesystemOperator $filesystem)
    {
    }

    public function getDecorated(): AbstractConfigLoader
    {
        throw new DecorationPatternException(self::class);
    }

    public function load(string $themeId, Context $context): FrontendPluginConfiguration
    {
        $path = \sprintf('theme-config/%s.json', $themeId);

        if (!$this->filesystem->fileExists($path)) {
            throw new \RuntimeException('Cannot find theme configuration. Did you run bin/console theme:dump');
        }

        $fileContent = $this->filesystem->read($path);
        \assert(\is_string($fileContent));
        $fileObject = json_decode($fileContent, true, 512, \JSON_THROW_ON_ERROR);

        $fileObject = $this->prepareCollections($fileObject);

        $config = new FrontendPluginConfiguration('');
        $config->assign($fileObject);

        return $config;
    }

    private function prepareCollections(array $fileObject): array
    {
        $fileObject['styleFiles'] = array_map(fn (array $file) => (new File(''))->assign($file), $fileObject['styleFiles']);

        $fileObject['scriptFiles'] = array_map(fn (array $file) => (new File(''))->assign($file), $fileObject['scriptFiles']);

        $fileObject['styleFiles'] = new FileCollection($fileObject['styleFiles']);
        $fileObject['scriptFiles'] = new FileCollection($fileObject['scriptFiles']);

        return $fileObject;
    }
}
