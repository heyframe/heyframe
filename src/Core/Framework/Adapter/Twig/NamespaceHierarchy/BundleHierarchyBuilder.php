<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Adapter\Twig\NamespaceHierarchy;

use Doctrine\DBAL\Connection;
use HeyFrame\Core\Framework\Bundle;
use HeyFrame\Core\Framework\Log\Package;
use Symfony\Component\HttpKernel\KernelInterface;

#[Package('framework')]
class BundleHierarchyBuilder implements TemplateNamespaceHierarchyBuilderInterface
{
    /**
     * @internal
     */
    public function __construct(
        private readonly KernelInterface $kernel,
    ) {
    }

    public function buildNamespaceHierarchy(array $namespaceHierarchy): array
    {
        /*
         * Priority system: Lower integer = higher precedence
         * Example: -2 overrides 0, which overrides 1
         * Used only for sorting, then discarded
         */
        $bundles = [];

        foreach ($this->kernel->getBundles() as $bundle) {
            if (!$bundle instanceof Bundle) {
                continue;
            }

            $bundlePath = $bundle->getPath();

            $directory = $bundlePath . '/Resources/views';

            if (!\is_dir($directory)) {
                continue;
            }

            $bundles[$bundle->getName()] = $bundle->getTemplatePriority();
        }

        // HeyFrame registers bundles in reverse order
        $bundles = array_reverse($bundles);


        $extensions = $bundles;
        asort($extensions);

        // Chain with existing hierarchy
        return array_merge(
            $extensions,
            $namespaceHierarchy
        );
    }
}
