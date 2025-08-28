<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Adapter\Twig\NamespaceHierarchy;

use HeyFrame\Core\Framework\Log\Package;

#[Package('framework')]
interface TemplateNamespaceHierarchyBuilderInterface
{
    /**
     * Gets the current hierarchy as param and can extend and modify the hierarchy.
     * Needs to return the new hierarchy.
     * Example hierarchy structure:
     * [
     *     'Storefront',
     *     'SwagPayPal',
     *     'MyOwnTheme',
     * ]
     *
     * @param array<string> $namespaceHierarchy
     *
     * @return array<string>
     */
    public function buildNamespaceHierarchy(array $namespaceHierarchy): array;
}
