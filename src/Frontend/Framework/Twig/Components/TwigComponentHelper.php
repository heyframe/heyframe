<?php declare(strict_types=1);

namespace HeyFrame\Frontend\Framework\Twig\Components;

use HeyFrame\Core\Framework\Adapter\Twig\NamespaceHierarchy\NamespaceHierarchyBuilder;
use HeyFrame\Core\Framework\Log\Package;
use Symfony\Component\Filesystem\Path;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\UX\TwigComponent\ComponentFactory;

#[Package('framework')]
class TwigComponentHelper
{
    private const MAIN_NAMESPACE = 'Frontend';

    /**
     * @param array<string, array{path: string}> $bundlesMetadata
     *
     * @internal
     */
    public function __construct(
        private string $componentDirectory,
        private array $bundlesMetadata,
        private readonly NamespaceHierarchyBuilder $namespaceHierarchyBuilder,
        private readonly ComponentFactory $componentFactory,
    ) {
    }

    public function getComponents(bool $includeMetadata = false): TwigComponentCollection
    {
        $components = new TwigComponentCollection();

        foreach ($this->findComponentsByTemplate() as $component) {
            if ($includeMetadata) {
                $componentMetadata = $this->componentFactory->metadataFor($component->getName());
                $component->setMetadata($componentMetadata);
            }

            $components->add($component);
        }

        return $components;
    }

    public function getComponentFromTemplate(SplFileInfo $template, string $componentNamespace): TwigComponent
    {
        $componentName = $this->getComponentNameFromPath($template->getRelativePathname());

        $component = new TwigComponent(
            $componentName,
            $template->getRealPath(),
            $componentNamespace
        );

        return $component;
    }

    /**
     * @return array<string, TwigComponent>
     */
    private function findComponentsByTemplate(): array
    {
        $dirs = $this->getBundleDirs();

        $components = [];
        $finderTemplates = new Finder();
        $finderTemplates->files()
            ->in(array_keys($dirs))
            ->notPath('/_')
            ->name('*.html.twig')
        ;

        foreach ($finderTemplates as $template) {
            $componentNamespace = $this->getComponentNamespace($template->getRealPath(), $dirs);
            $component = $this->getComponentFromTemplate($template, $componentNamespace);

            $components[$component->getName()] = $component;
        }

        return $components;
    }

    /**
     * @param array<string, string> $dirs
     */
    private function getComponentNamespace(string $templatePath, array $dirs): string
    {
        // Find the closest matching parent directory.
        $templateDir = Path::getDirectory($templatePath);

        // Check for exact match first.
        if (isset($dirs[$templateDir])) {
            return $dirs[$templateDir];
        }

        // Check if template is under any of the registered bundle directories.
        foreach ($dirs as $dir => $namespace) {
            if (str_starts_with($templateDir, $dir)) {
                return $namespace;
            }
        }

        return self::MAIN_NAMESPACE;
    }

    /**
     * @return array<string, string>
     */
    private function getBundleDirs(): array
    {
        $namespaceHierarchy = $this->namespaceHierarchyBuilder->buildHierarchy();
        $namespaces = array_keys($namespaceHierarchy);

        $dirs = [];

        foreach ($namespaces as $namespace) {
            if (!isset($this->bundlesMetadata[$namespace])) {
                continue;
            }

            $path = $this->bundlesMetadata[$namespace]['path'];
            $componentDir = Path::join($path, $this->componentDirectory);

            if (!is_dir($componentDir)) {
                continue;
            }

            $dirs[$componentDir] = $namespace;
        }

        return $dirs;
    }

    private function getComponentAppPath(string $appPath, string $templatePath): string
    {
        if (str_starts_with($templatePath, 'components/')) {
            $templatePath = str_replace('components/', '', $templatePath);
        }

        return Path::join($appPath, $this->componentDirectory, $templatePath);
    }

    private function getComponentNameFromPath(string $templateRelativePath): string
    {
        if (str_starts_with($templateRelativePath, 'components/')) {
            $templateRelativePath = str_replace('components/', '', $templateRelativePath);
        }

        $componentName = str_replace(\DIRECTORY_SEPARATOR, ':', $templateRelativePath);
        $componentName = substr($componentName, 0, -10); // remove file extension ".html.twig"

        return $componentName;
    }
}
