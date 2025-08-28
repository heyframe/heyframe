<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\App\Command;

use HeyFrame\Core\Framework\Adapter\Console\HeyFrameStyle;
use HeyFrame\Core\Framework\App\ShopId\FingerprintComparisonResult;
use HeyFrame\Core\Framework\App\ShopId\FingerprintGenerator;
use HeyFrame\Core\Framework\App\ShopId\ShopId;
use HeyFrame\Core\Framework\App\ShopId\ShopIdProvider;
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
class CheckShopIdCommand extends Command
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

        $shopIdConfig = $this->systemConfigService->get(ShopIdProvider::SHOP_ID_SYSTEM_CONFIG_KEY_V2)
            ?? $this->systemConfigService->get(ShopIdProvider::SHOP_ID_SYSTEM_CONFIG_KEY);

        if (!\is_array($shopIdConfig)) {
            $io->success('No shop ID has been generated yet.');

            return self::SUCCESS;
        }

        $shopId = ShopId::fromSystemConfig($shopIdConfig);
        $result = $this->fingerprintGenerator->matchFingerprints($shopId->fingerprints);

        $this->renderShopIdTable($io, $shopId);
        $this->renderFingerprintsTable($io, $result);
        $this->renderResult($io, $result);

        return $result->isMatching() ? self::SUCCESS : self::FAILURE;
    }

    private function renderShopIdTable(HeyFrameStyle $io, ShopId $shopId): void
    {
        $shopIdTable = new Table($io);
        $shopIdTable->setVertical();
        $shopIdTable->setHeaders(['Shop ID', 'Version']);
        $shopIdTable->addRow([$shopId->id, $shopId->version]);
        $shopIdTable->render();

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
