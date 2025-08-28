<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\App\Command;

use HeyFrame\Core\Framework\Adapter\Console\HeyFrameStyle;
use HeyFrame\Core\Framework\App\AppCollection;
use HeyFrame\Core\Framework\App\AppEntity;
use HeyFrame\Core\Framework\App\Lifecycle\AbstractAppLifecycle;
use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityRepository;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Criteria;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Storefront\Theme\ThemeLifecycleHandler;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @internal only for use by the app-system
 */
#[AsCommand(
    name: 'app:uninstall',
    description: 'Uninstalls an app',
)]
#[Package('framework')]
class UninstallAppCommand extends Command
{
    /**
     * @param EntityRepository<AppCollection> $appRepository
     */
    public function __construct(
        private readonly AbstractAppLifecycle $appLifecycle,
        private readonly EntityRepository $appRepository
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new HeyFrameStyle($input, $output);

        $name = $input->getArgument('name');

        if (!\is_string($name)) {
            throw new \InvalidArgumentException('Argument $name must be an string');
        }

        $context = Context::createCLIContext();
        if ($input->getOption('skip-theme-compile')) {
            $context->addState(ThemeLifecycleHandler::STATE_SKIP_THEME_COMPILATION);
        }

        $app = $this->getAppByName($name, $context);

        if (!$app) {
            $io->error(\sprintf('No app with name "%s" installed.', $name));

            return self::FAILURE;
        }

        $keepUserData = $input->getOption('keep-user-data');

        $this->appLifecycle->delete(
            $app->getName(),
            [
                'id' => $app->getId(),
                'roleId' => $app->getAclRoleId(),
            ],
            $context,
            $keepUserData
        );

        $io->success('App uninstalled successfully.');

        return self::SUCCESS;
    }

    protected function configure(): void
    {
        $this->addArgument('name', InputArgument::REQUIRED, 'The name of the app');
        $this->addOption(
            'keep-user-data',
            null,
            InputOption::VALUE_NONE,
            'Keep user data of the app'
        );
        $this->addOption(
            'skip-theme-compile',
            null,
            InputOption::VALUE_NONE,
            'Use this option to skip recompiling of all themes'
        );
    }

    private function getAppByName(string $name, Context $context): ?AppEntity
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('name', $name));

        return $this->appRepository->search($criteria, $context)->getEntities()->first();
    }
}
