<?php declare(strict_types=1);

namespace HeyFrame\Core\Maintenance\Channel\Command;

use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityRepository;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Criteria;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelCollection;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @internal should be used over the CLI only
 */
#[AsCommand(
    name: 'channel:maintenance:enable',
    description: 'Enable maintenance mode for a sales channel',
)]
#[Package('discovery')]
class ChannelMaintenanceEnableCommand extends Command
{
    protected bool $setMaintenanceMode = true;

    /**
     * @param EntityRepository<ChannelCollection> $channelRepository
     */
    public function __construct(
        private readonly EntityRepository $channelRepository
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument(
            'ids',
            InputArgument::IS_ARRAY | InputArgument::OPTIONAL,
            'Which Sales Channels do you want to update maintenance mode for? (Optional when --all flag is used)',
            []
        )->addOption(
            'all',
            'a',
            InputOption::VALUE_NONE,
            'Set maintenance mode for all sales channels'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $context = Context::createCLIContext();
        $criteria = new Criteria();

        if (!$input->getOption('all')) {
            $ids = $input->getArgument('ids');
            if ($ids === []) {
                $output->write('No sales channels were updated. Provide id(s) or run with --all option.');

                return self::SUCCESS;
            }

            $criteria->setIds($ids);
        }

        /** @var array<string> $channelIds */
        $channelIds = $this->channelRepository->searchIds($criteria, $context)->getIds();

        if (empty($channelIds)) {
            $output->write('No sales channels were updated');

            return self::SUCCESS;
        }

        $update = array_map(fn (string $id) => [
            'id' => $id,
            'maintenance' => $this->setMaintenanceMode,
        ], $channelIds);

        $this->channelRepository->update($update, $context);

        $output->write(\sprintf('Updated maintenance mode for %d sales channel(s)', \count($channelIds)));

        return self::SUCCESS;
    }
}
