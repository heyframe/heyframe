<?php declare(strict_types=1);

namespace HeyFrame\Core\Installer;

use HeyFrame\Core\Framework\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @internal
 */
class Installer extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);
    }
}
