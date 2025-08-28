<?php declare(strict_types=1);

namespace HeyFrame\Core\Maintenance\System\Command;

use HeyFrame\Core\DevOps\Environment\EnvironmentHelper;
use HeyFrame\Core\Framework\Adapter\Console\HeyFrameStyle;
use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Update\Event\UpdatePostPrepareEvent;
use HeyFrame\Core\Framework\Update\Event\UpdatePrePrepareEvent;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @internal should be used over the CLI only
 */
#[AsCommand(
    name: 'system:update:prepare',
    description: 'Prepares the update process',
)]
#[Package('framework')]
class SystemUpdatePrepareCommand extends Command
{
    public function __construct(private readonly ContainerInterface $container, private readonly string $heyframeVersion)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output = new HeyFrameStyle($input, $output);

        $dsn = trim((string) EnvironmentHelper::getVariable('DATABASE_URL', getenv('DATABASE_URL')));
        if ($dsn === '') {
            $output->note('Environment variable \'DATABASE_URL\' not defined. Skipping ' . $this->getName() . '...');

            return self::SUCCESS;
        }

        $output->writeln('Run Update preparations');

        $context = Context::createCLIContext();

        // TODO: get new version (from composer.lock?)
        $newVersion = '';

        $eventDispatcher = $this->container->get('event_dispatcher');
        $eventDispatcher->dispatch(new UpdatePrePrepareEvent($context, $this->heyframeVersion, $newVersion));

        $eventDispatcher->dispatch(new UpdatePostPrepareEvent($context, $this->heyframeVersion, $newVersion));

        return self::SUCCESS;
    }
}
