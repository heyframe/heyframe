<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\DependencyInjection\CompilerPass;

use HeyFrame\Core\Framework\Event\BusinessEventRegistry;
use HeyFrame\Core\Framework\Log\Package;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

#[Package('framework')]
class BusinessEventRegisterCompilerPass implements CompilerPassInterface
{
    /**
     * @param class-string[] $classes
     */
    public function __construct(private readonly array $classes)
    {
    }

    public function process(ContainerBuilder $container): void
    {
        $definition = $container->getDefinition(BusinessEventRegistry::class);
        $definition->addMethodCall('addClasses', [$this->classes]);
    }
}
