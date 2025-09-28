<?php

namespace Scripts\Boot;
use HeyFrame\Core\Kernel;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class ScriptKernel extends Kernel
{
    protected function build(ContainerBuilder $container): void
    {
        foreach ($container->getDefinitions() as $id => $definition) {
            if ($definition->isAbstract()) {
                continue;
            }
            $definition->setPublic(true);
        }
    }
}
