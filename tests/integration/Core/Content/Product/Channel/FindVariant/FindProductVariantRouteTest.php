<?php declare(strict_types=1);

namespace HeyFrame\Tests\Integration\Core\Content\Product\Channel\FindVariant;

use HeyFrame\Core\Content\Product\Channel\FindVariant\FindProductVariantRoute;
use HeyFrame\Core\Content\Product\Exception\VariantNotFoundException;
use HeyFrame\Core\Content\Product\ProductCollection;
use HeyFrame\Core\Content\Test\Product\ProductBuilder;
use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityRepository;
use HeyFrame\Core\Framework\Test\TestCaseBase\IntegrationTestBehaviour;
use HeyFrame\Core\System\Channel\ChannelContext;
use HeyFrame\Core\System\Channel\Context\ChannelContextFactory;
use HeyFrame\Core\Test\Stub\Framework\IdsCollection;
use HeyFrame\Core\Test\TestDefaults;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

/**
 * @internal
 */
#[CoversClass(FindProductVariantRoute::class)]
class FindProductVariantRouteTest extends TestCase
{
    use IntegrationTestBehaviour;

    /**
     * @var EntityRepository<ProductCollection>
     */
    private EntityRepository $repository;

    private ChannelContext $context;

    private FindProductVariantRoute $findProductVariantRoute;

    private IdsCollection $ids;

    protected function setUp(): void
    {
        $this->repository = static::getContainer()->get('product.repository');

        $this->context = static::getContainer()->get(ChannelContextFactory::class)
            ->create('test', TestDefaults::CHANNEL);

        $this->findProductVariantRoute = static::getContainer()->get(FindProductVariantRoute::class);

        $this->ids = new IdsCollection();

        $this->createProduct();

        parent::setUp();
    }

    public function testFindVariant(): void
    {
        $options = [
            $this->ids->get('Color') => $this->ids->get('Red'),
            $this->ids->get('Size') => $this->ids->get('XL'),
        ];

        $switched = $this->ids->get('Color');

        $result = $this->findProductVariantRoute->load(
            $this->ids->get('base'),
            new Request(
                [
                    'switchedGroup' => $switched,
                    'options' => $options,
                ]
            ),
            $this->context
        );

        static::assertSame($this->ids->get('redXL'), $result->getFoundCombination()->getVariantId());
    }

    public function testFindToNotCombinable(): void
    {
        // update red-xl to inactive
        $this->repository->update(
            [
                ['id' => $this->ids->get('redXL'), 'active' => false],
            ],
            Context::createDefaultContext()
        );

        $switched = $this->ids->get('Color');

        $options = [
            $this->ids->get('Color') => $this->ids->get('Red'),
            $this->ids->get('Size') => $this->ids->get('XL'),
        ];

        // wished to switch to red-xl but this variant is not available (active = false).
        // should switch to next matching size
        $result = $this->findProductVariantRoute->load(
            $this->ids->get('base'),
            new Request(
                [
                    'switchedGroup' => $switched,
                    'options' => $options,
                ]
            ),
            $this->context
        );

        static::assertSame($this->ids->get('redL'), $result->getFoundCombination()->getVariantId());
    }

    public function testFindNoCombinable(): void
    {
        $switched = $this->ids->get('new');

        $options = [
            $this->ids->get('new') => $this->ids->get('new'),
        ];

        static::expectException(VariantNotFoundException::class);
        static::expectExceptionMessage(
            'Variant for productId '
            . $this->ids->get('base') . ' with options {"' . $this->ids->get('new') . '":"' . $this->ids->get('new')
            . '"} not found.'
        );

        $this->findProductVariantRoute->load(
            $this->ids->get('base'),
            new Request(
                [
                    'switchedGroup' => $switched,
                    'options' => $options,
                ]
            ),
            $this->context
        );
    }

    private function createProduct(): void
    {
        (new ProductBuilder($this->ids, 'base', 10))->configuratorSetting(
            'Red',
            'Color'
        )->configuratorSetting(
            'Green',
            'Color'
        )->configuratorSetting(
            'XL',
            'Size'
        )->configuratorSetting(
            'L',
            'Size'
        )->visibility()->price(10)->write(static::getContainer());

        (new ProductBuilder($this->ids, 'redXL', 10))->visibility()->parent('base')->price(10)->option(
            'Red',
            'Color'
        )->option('XL', 'Size')->stock(10)->write(static::getContainer());
        (new ProductBuilder($this->ids, 'greenXL', 10))->visibility()->parent('base')->price(10)->option(
            'Green',
            'Color'
        )->option('XL', 'Size')->stock(10)->write(static::getContainer());
        (new ProductBuilder($this->ids, 'redL', 10))->visibility()->parent('base')->price(10)->option(
            'Red',
            'Color'
        )->option('L', 'Size')->stock(10)->write(static::getContainer());
        (new ProductBuilder($this->ids, 'greenL', 10))->visibility()->parent('base')->price(10)->option(
            'Green',
            'Color'
        )->option('L', 'Size')->stock(10)->write(static::getContainer());
    }
}
