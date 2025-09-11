<?php declare(strict_types=1);

namespace HeyFrame\Elasticsearch\Framework\Command;

use HeyFrame\Core\Checkout\Customer\Aggregate\CustomerGroup\CustomerGroupDefinition;
use HeyFrame\Core\Checkout\Customer\CustomerDefinition;
use HeyFrame\Core\Checkout\Order\OrderDefinition;
use HeyFrame\Core\Checkout\Payment\PaymentMethodDefinition;
use HeyFrame\Core\Checkout\Promotion\PromotionDefinition;
use HeyFrame\Core\Checkout\Shipping\ShippingMethodDefinition;
use HeyFrame\Core\Content\Cms\CmsPageDefinition;
use HeyFrame\Core\Content\LandingPage\LandingPageDefinition;
use HeyFrame\Core\Content\Media\MediaDefinition;
use HeyFrame\Core\Content\Product\Aggregate\ProductManufacturer\ProductManufacturerDefinition;
use HeyFrame\Core\Content\Product\ProductDefinition;
use HeyFrame\Core\Content\Property\PropertyGroupDefinition;
use HeyFrame\Core\Framework\Adapter\Console\HeyFrameStyle;
use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelDefinition;
use HeyFrame\Elasticsearch\Admin\AdminSearcher;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * @internal
 */
#[AsCommand(
    name: 'es:admin:test',
    description: 'Allows you to test the admin search index',
)]
#[Package('inventory')]
final class ElasticsearchAdminTestCommand extends Command
{
    private SymfonyStyle $io;

    /**
     * @internal
     */
    public function __construct(private readonly AdminSearcher $searcher)
    {
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $this->addArgument('term', InputArgument::REQUIRED);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io = new HeyFrameStyle($input, $output);

        $term = $input->getArgument('term');
        $entities = [
            CmsPageDefinition::ENTITY_NAME,
            CustomerDefinition::ENTITY_NAME,
            CustomerGroupDefinition::ENTITY_NAME,
            LandingPageDefinition::ENTITY_NAME,
            ProductManufacturerDefinition::ENTITY_NAME,
            MediaDefinition::ENTITY_NAME,
            OrderDefinition::ENTITY_NAME,
            PaymentMethodDefinition::ENTITY_NAME,
            ProductDefinition::ENTITY_NAME,
            PromotionDefinition::ENTITY_NAME,
            PropertyGroupDefinition::ENTITY_NAME,
            ChannelDefinition::ENTITY_NAME,
            ShippingMethodDefinition::ENTITY_NAME,
        ];

        $result = $this->searcher->search($term, $entities, Context::createCLIContext());

        $rows = [];
        foreach ($result as $data) {
            $rows[] = [$data['index'], $data['indexer'], $data['total']];
        }

        $this->io->table(['Index', 'Indexer', 'total'], $rows);

        return self::SUCCESS;
    }
}
