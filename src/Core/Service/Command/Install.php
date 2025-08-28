<?php declare(strict_types=1);

namespace HeyFrame\Core\Service\Command;

use HeyFrame\Core\Framework\Adapter\Console\HeyFrameStyle;
use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Service\LifecycleManager;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[Package('framework')]
#[AsCommand(
    name: 'services:install',
    description: 'Install all services'
)]
class Install extends Command
{
    /**
     * @internal
     */
    public function __construct(private readonly LifecycleManager $manager)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new HeyFrameStyle($input, $output);

        $io->title('Installing services...');

        if (!$this->manager->enabled()) {
            $io->error('Services are disabled. Please enable them to install services.');

            return Command::FAILURE;
        }

        $installed = $this->manager->install(Context::createCLIContext());

        if (empty($installed)) {
            $io->info('No services were installed');
        } else {
            $io->success(\sprintf('Done. Installed %s', implode(', ', $installed)));
        }

        return Command::SUCCESS;
    }
}
