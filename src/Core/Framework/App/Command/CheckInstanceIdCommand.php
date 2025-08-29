<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\App\Command;

use HeyFrame\Core\Framework\Adapter\Console\HeyFrameStyle;
use HeyFrame\Core\Framework\App\InstanceId\FingerprintComparisonResult;
use HeyFrame\Core\Framework\App\InstanceId\FingerprintGenerator;
use HeyFrame\Core\Framework\App\InstanceId\InstanceId;
use HeyFrame\Core\Framework\App\InstanceId\InstanceIdProvider;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\SystemConfig\SystemConfigService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @internal
 */
#[AsCommand(
    name: 'app:shop-id:check',
    description: 'Check if a shop ID change is suggested',
)]
#[Package('framework')]
class CheckInstanceIdCommand extends Command
{
    public function __construct(
        private readonly SystemConfigService $systemConfigService,
        private readonly FingerprintGenerator $fingerprintGenerator,
    ) {
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new HeyFrameStyle($input, $output);

        $instanceIdConfig = $this->systemConfigService->get(InstanceIdProvider::SHOP_ID_SYSTEM_CONFIG_KEY_V2)
            ?? $this->systemConfigService->get(InstanceIdProvider::SHOP_ID_SYSTEM_CONFIG_KEY);

        if (!\is_array($instanceIdConfig)) {
            $io->success('No shop ID has been generated yet.');

            return self::SUCCESS;
        }

        $instanceId = InstanceId::fromSystemConfig($instanceIdConfig);
        $result = $this->fingerprintGenerator->matchFingerprints($instanceId->fingerprints);

        $this->renderInstanceIdTable($io, $instanceId);
        $this->renderFingerprintsTable($io, $result);
        $this->renderResult($io, $result);

        return $result->isMatching() ? self::SUCCESS : self::FAILURE;
    }

    private function renderInstanceIdTable(HeyFrameStyle $io, InstanceId $instanceId): void
    {
        $instanceIdTable = new Table($io);
        $instanceIdTable->setVertical();
        $instanceIdTable->setHeaders(['Shop ID', 'Version']);
        $instanceIdTable->addRow([$instanceId->id, $instanceId->version]);
        $instanceIdTable->render();

        $io->writeln('');
    }

    private function renderFingerprintsTable(HeyFrameStyle $io, FingerprintComparisonResult $result): void
    {
        $fingerprintsTable = new Table($io);
        $fingerprintsTable->setHeaders(['Fingerprint', 'Old Value', 'New Value', 'Score', 'State']);

        foreach ($result->mismatchingFingerprints as $fingerprint) {
            $fingerprintsTable->addRow([$fingerprint->identifier, $fingerprint->storedStamp, $fingerprint->expectedStamp ?? 'NULL', $fingerprint->score, '<fg=red>✘ MISMATCH</>']);
        }

        foreach ($result->matchingFingerprints as $fingerprint) {
            $fingerprintsTable->addRow([$fingerprint->identifier, $fingerprint->storedStamp, $fingerprint->storedStamp, $fingerprint->score, '<fg=green>✔ MATCH</>']);
        }

        $fingerprintsTable->render();

        $io->writeln('');
    }

    private function renderResult(HeyFrameStyle $io, FingerprintComparisonResult $result): void
    {
        if ($result->isMatching()) {
            $io->success('Shop ID change not suggested.');
        } else {
            $io->warning(\sprintf('Shop ID change suggested (Score: %s/%s). Run "bin/console app:shop-id:change" to change the shop ID.', $result->score, $result->threshold));
        }
    }
}
