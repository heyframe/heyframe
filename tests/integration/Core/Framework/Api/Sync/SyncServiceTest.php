<?php declare(strict_types=1);

namespace HeyFrame\Tests\Integration\Core\Framework\Api\Sync;

use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;
use HeyFrame\Core\Content\Test\Product\ProductBuilder;
use HeyFrame\Core\Defaults;
use HeyFrame\Core\Framework\Api\Sync\SyncBehavior;
use HeyFrame\Core\Framework\Api\Sync\SyncOperation;
use HeyFrame\Core\Framework\Api\Sync\SyncService;
use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\DataAbstractionLayer\Event\EntityWrittenContainerEvent;
use HeyFrame\Core\Framework\Test\TestCaseBase\IntegrationTestBehaviour;
use HeyFrame\Core\Framework\Test\TestCaseHelper\CallableClass;
use HeyFrame\Core\Framework\Uuid\Uuid;
use HeyFrame\Core\Test\Stub\Framework\IdsCollection;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class SyncServiceTest extends TestCase
{
    use IntegrationTestBehaviour;

    private SyncService $service;

    private Connection $connection;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = static::getContainer()->get(SyncService::class);
        $this->connection = static::getContainer()->get(Connection::class);
    }

    public function testSendNoneExistingId(): void
    {
        $ids = new IdsCollection();

        $operations = [
            new SyncOperation('delete-price', 'product_price', 'delete', [['id' => $ids->get('not-existing-price')]]),
        ];

        $result = $this->service->sync($operations, Context::createDefaultContext(), new SyncBehavior());

        static::assertSame([], $result->getDeleted());

        $expected = ['product_price' => [$ids->get('not-existing-price')]];

        static::assertSame($expected, $result->getNotFound());
    }

    public function testDeleteProductMediaAndUpdateProduct(): void
    {
        $ids = new IdsCollection();
        $product = (new ProductBuilder($ids, 'p1'))
            ->price(100)
            ->media('media-1')
            ->media('media-2')
            ->media('media-3')
            ->build();

        static::getContainer()->get('product.repository')
            ->create([$product], Context::createDefaultContext());

        $operations = [
            new SyncOperation('delete-media', 'product_media', 'delete', [['id' => $ids->get('media-2')]]),
            new SyncOperation('update-product', 'product', 'upsert', [['id' => $ids->get('p1'), 'media' => [['id' => $ids->get('media-3'), 'position' => 10]]]]),
        ];

        $this->service->sync($operations, Context::createDefaultContext(), new SyncBehavior());
        $exists = $this->connection->fetchAllAssociative(
            'SELECT id FROM product_media WHERE id IN (:ids)',
            ['ids' => Uuid::fromHexToBytesList($ids->getList(['media-2']))],
            ['ids' => ArrayParameterType::BINARY]
        );
        static::assertEmpty($exists);
    }

    public function testSingleOperationParameter(): void
    {
        $ids = new IdsCollection();

        $dispatcher = static::getContainer()->get('event_dispatcher');

        $listener = $this
            ->getMockBuilder(CallableClass::class)
            ->getMock();

        $listener->expects($this->once())
            ->method('__invoke');

        $this->addEventListener($dispatcher, EntityWrittenContainerEvent::class, $listener);

        $operations = [
            new SyncOperation('write', 'country', SyncOperation::ACTION_UPSERT, [
                ['id' => $ids->create('c1'), 'name' => 'first country'],
                ['id' => $ids->create('c2'), 'name' => 'second country'],
            ]),
        ];

        $this->service->sync($operations, Context::createDefaultContext(), new SyncBehavior());
    }

    public function testOrderOfActionsFromDeleteInsertUpdateSameId(): void
    {
        $ids = new IdsCollection();

        $products = [
            (new ProductBuilder($ids, 'p1'))
                ->price(100)
                ->build(),
        ];

        static::getContainer()->get('product.repository')->create($products, Context::createDefaultContext());

        $operations = [
            new SyncOperation('delete', 'product', SyncOperation::ACTION_DELETE, [
                ['id' => $ids->get('p1')],
            ]),
            new SyncOperation('create', 'product', SyncOperation::ACTION_UPSERT, [
                [
                    'id' => $ids->get('p1'),
                    'price' => [
                        ['currencyId' => Defaults::CURRENCY, 'gross' => 200, 'net' => 200, 'linked' => true],
                    ],
                    'productNumber' => 'test',
                    'stock' => 10,
                    'productType' => 'membership',
                    'name' => 'test',
                ],
            ]),
            new SyncOperation('update', 'product', SyncOperation::ACTION_UPSERT, [
                [
                    'id' => $ids->get('p1'),
                    'price' => [
                        ['currencyId' => Defaults::CURRENCY, 'gross' => 300, 'net' => 300, 'linked' => true],
                    ],
                ],
            ]),
        ];

        $this->service->sync($operations, Context::createDefaultContext(), new SyncBehavior());

        $productPrice = $this->connection->fetchOne(
            'SELECT price FROM product WHERE id = :id',
            ['id' => Uuid::fromHexToBytes($ids->get('p1'))]
        );

        static::assertIsString($productPrice);
        $productPrice = json_decode($productPrice, true);
        $productPrice = array_shift($productPrice);
        static::assertSame(300.0, $productPrice['gross']);
    }
}
