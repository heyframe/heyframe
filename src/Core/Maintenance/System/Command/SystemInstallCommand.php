<?php declare(strict_types=1);

namespace HeyFrame\Core\Maintenance\System\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;

/**
 * @internal should be used over the CLI only
 */
#[AsCommand(
    name: 'system:install',
    description: 'Installs the Shopware 6 system',
)]
class SystemInstallCommand extends Command
{
}
