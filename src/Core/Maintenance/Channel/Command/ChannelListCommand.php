<?php declare(strict_types=1);

namespace HeyFrame\Core\Maintenance\Channel\Command;

use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityRepository;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Criteria;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\Aggregate\ChannelDomain\ChannelDomainCollection;
use HeyFrame\Core\System\Channel\Aggregate\ChannelDomain\ChannelDomainEntity;
use HeyFrame\Core\System\Channel\ChannelCollection;
use HeyFrame\Core\System\Currency\CurrencyCollection;
use HeyFrame\Core\System\Currency\CurrencyEntity;
use HeyFrame\Core\System\Language\LanguageCollection;
use HeyFrame\Core\System\Language\LanguageEntity;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @internal should be used over the CLI only
 */
#[AsCommand(
    name: 'channel:list',
    description: 'Lists all sales channels',
)]
#[Package('discovery')]
class ChannelListCommand extends Command
{
    /**
     * @var list<string>
     */
    private static array $headers = [
        'id',
        'Name',
        'Active',
        'Maintenance',
        'Default Language',
        'Languages',
        'Default Currency',
        'Currencies',
        'Domains',
    ];

    /**
     * @param EntityRepository<ChannelCollection> $channelRepository
     */
    public function __construct(private readonly EntityRepository $channelRepository)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption(
            'output',
            '0',
            InputOption::VALUE_OPTIONAL,
            'Output mode. Available options: "table", "json"',
            'table'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $criteria = new Criteria();
        $criteria->addAssociations(['language', 'languages', 'currency', 'currencies', 'domains']);
        $channels = $this->channelRepository->search($criteria, Context::createCLIContext())->getEntities();

        $data = [];
        foreach ($channels as $channel) {
            $language = $channel->getLanguage();
            $languages = $channel->getLanguages() ?? new LanguageCollection();
            $currency = $channel->getCurrency();
            $currencies = $channel->getCurrencies() ?? new CurrencyCollection();
            $domains = $channel->getDomains() ?? new ChannelDomainCollection();

            $data[] = [
                $channel->getId(),
                $channel->getName() ?? 'n/a',
                $channel->getActive() ? 'active' : 'inactive',
                $channel->isMaintenance() ? 'on' : 'off',
                $language?->getName() ?? 'n/a',
                $languages->map(fn (LanguageEntity $language) => $language->getName()),
                $currency?->getName() ?? 'n/a',
                $currencies->map(fn (CurrencyEntity $currency) => $currency->getName()),
                $domains->map(fn (ChannelDomainEntity $domain) => $domain->getUrl()),
            ];
        }

        if ($input->getOption('output') === 'json') {
            return $this->renderJson($output, $data);
        }

        return $this->renderTable($output, $data);
    }

    /**
     * @param list<list<string|array<string, string>>> $data
     */
    private function renderJson(OutputInterface $output, array $data): int
    {
        $json = [];

        foreach ($data as $row) {
            $jsonItem = [];
            foreach ($row as $item => $value) {
                $jsonItem[mb_strtolower((string) (self::$headers[$item] ?? $item))] = $value;
            }
            $json[] = $jsonItem;
        }

        $encoded = json_encode($json, \JSON_THROW_ON_ERROR);

        $output->write($encoded);

        return self::SUCCESS;
    }

    /**
     * @param list<list<string|array<string, string>>> $data
     */
    private function renderTable(OutputInterface $output, array $data): int
    {
        $table = new Table($output);
        $table->setHeaders(self::$headers);

        // Normalize data
        foreach ($data as $rowKey => $row) {
            foreach ($row as $columnKey => $column) {
                if (\is_array($column)) {
                    $data[$rowKey][$columnKey] = implode(', ', $column);
                }
            }
        }

        $table->addRows($data);

        $table->render();

        return self::SUCCESS;
    }
}
