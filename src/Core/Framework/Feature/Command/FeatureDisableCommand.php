<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Feature\Command;

use HeyFrame\Core\Framework\Adapter\Cache\CacheClearer;
use HeyFrame\Core\Framework\Feature\FeatureFlagRegistry;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * @internal
 */
#[AsCommand(name: 'feature:disable', description: 'Disable feature flags')]
final class FeatureDisableCommand extends Command
{
    /**
     * @internal
     */
    public function __construct(
        private readonly FeatureFlagRegistry $featureFlagService,
        private readonly CacheClearer $cacheClearer
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('features', InputArgument::REQUIRED | InputArgument::IS_ARRAY, 'The feature names to disable');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var array<string> $featuresToDisable */
        $featuresToDisable = array_unique($input->getArgument('features'));

        foreach ($featuresToDisable as $feature) {
            $this->featureFlagService->disable($feature);
        }

        $io = new SymfonyStyle($input, $output);

        $this->cacheClearer->clear();

        $io->info('The cache was cleared.');
        $io->success('The following features were disabled: ' . implode(', ', $featuresToDisable));

        return self::SUCCESS;
    }
}
