<?php declare(strict_types=1);

namespace HeyFrame\Tests\Integration\Core\Content\ContentSystem\Resolver;

use Doctrine\DBAL\Connection;
use HeyFrame\Core\Content\ContentSystem\ContentRoute\ContentRouteEntity;
use HeyFrame\Core\Content\ContentSystem\Resolver\LayoutResolver;
use HeyFrame\Core\Content\ContentSystem\Resolver\Struct\ResolvedData;
use HeyFrame\Core\Content\ContentSystem\Routing\Struct\RouteMatchResult;
use HeyFrame\Core\Defaults;
use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityRepository;
use HeyFrame\Core\Framework\Test\TestCaseBase\KernelTestBehaviour;
use HeyFrame\Core\Framework\Uuid\Uuid;
use HeyFrame\Core\System\Channel\ChannelContext;
use HeyFrame\Core\System\Channel\Context\ChannelContextFactory;
use HeyFrame\Core\Test\TestDefaults;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(LayoutResolver::class)]
class LayoutResolverTest extends TestCase
{
    use KernelTestBehaviour;

    private LayoutResolver $resolver;

    private Connection $connection;

    private Context $context;

    private ChannelContext $channelContext;

    private EntityRepository $productRepository;

    private EntityRepository $categoryRepository;

    private string $productId;

    private string $categoryId;

    private string $layoutId1;

    private string $layoutId2;

    private string $layoutId3;

    private string $channelId;

    protected function setUp(): void
    {
        $this->resolver = static::getContainer()->get(LayoutResolver::class);
        $this->connection = static::getContainer()->get(Connection::class);
        $this->context = Context::createDefaultContext();
        $this->productRepository = static::getContainer()->get('product.repository');
        $this->categoryRepository = static::getContainer()->get('category.repository');

        $channelContextFactory = static::getContainer()->get(ChannelContextFactory::class);
        $this->channelContext = $channelContextFactory->create(Uuid::randomHex(), TestDefaults::CHANNEL);
        $this->channelId = $this->channelContext->getChannel()->getId();

        // Create test data
        $this->productId = Uuid::randomHex();
        $this->categoryId = Uuid::randomHex();
        $this->layoutId1 = Uuid::randomHex();
        $this->layoutId2 = Uuid::randomHex();
        $this->layoutId3 = Uuid::randomHex();

        $this->createTestProduct();
        $this->createTestLayouts();
    }

    protected function tearDown(): void
    {
        // Clean up test data
        $this->connection->executeStatement('DELETE FROM content_layout_assignment WHERE channel_id = :scId', [
            'scId' => Uuid::fromHexToBytes($this->channelId),
        ]);
        $this->connection->executeStatement('DELETE FROM product_category WHERE product_id = :pid', [
            'pid' => Uuid::fromHexToBytes($this->productId),
        ]);
        $this->connection->executeStatement('DELETE FROM content_layout WHERE id IN (:id1, :id2, :id3)', [
            'id1' => Uuid::fromHexToBytes($this->layoutId1),
            'id2' => Uuid::fromHexToBytes($this->layoutId2),
            'id3' => Uuid::fromHexToBytes($this->layoutId3),
        ]);
    }

    public function testDirectProductLayoutResolution(): void
    {
        // Create direct product layout assignment
        $this->createLayoutAssignment('product', $this->productId, $this->layoutId1);

        $route = $this->createRoute([
            ['entity' => 'product'],
            ['entity' => null],
        ]);

        $resolvedData = new ResolvedData(['product_id' => $this->productId], []);
        $matchResult = new RouteMatchResult($route, []);

        $layoutId = $this->resolver->resolve($matchResult, $resolvedData, $this->channelContext);

        static::assertSame($this->layoutId1, $layoutId);
    }

    public function testCategoryLayoutViaManyToMany(): void
    {
        // Create category layout assignment
        $this->createLayoutAssignment('category', $this->categoryId, $this->layoutId2);

        // Link product to category
        $this->linkProductToCategory();

        $route = $this->createRoute([
            ['entity' => 'product'],
            ['via' => 'categories', 'entity' => 'category'],
            ['entity' => null],
        ]);

        $resolvedData = new ResolvedData(['product_id' => $this->productId], []);
        $matchResult = new RouteMatchResult($route, []);

        $layoutId = $this->resolver->resolve($matchResult, $resolvedData, $this->channelContext);

        static::assertSame($this->layoutId2, $layoutId);
    }

    public function testCascadePriority(): void
    {
        // Create all three: product > category > default
        $this->createLayoutAssignment('product', $this->productId, $this->layoutId1);
        $this->createLayoutAssignment('category', $this->categoryId, $this->layoutId2);
        $this->createLayoutAssignment(null, null, $this->layoutId3); // default

        $this->linkProductToCategory();

        $route = $this->createRoute([
            ['entity' => 'product'],
            ['via' => 'categories', 'entity' => 'category'],
            ['entity' => null],
        ]);

        $resolvedData = new ResolvedData(['product_id' => $this->productId], []);
        $matchResult = new RouteMatchResult($route, []);

        $layoutId = $this->resolver->resolve($matchResult, $resolvedData, $this->channelContext);

        // Product layout should win (highest priority)
        static::assertSame($this->layoutId1, $layoutId);
    }

    public function testDefaultFallback(): void
    {
        // Only create default layout
        $this->createLayoutAssignment(null, null, $this->layoutId3);

        $route = $this->createRoute([
            ['entity' => 'product'],
            ['entity' => null],
        ]);

        $resolvedData = new ResolvedData(['product_id' => $this->productId], []);
        $matchResult = new RouteMatchResult($route, []);

        $layoutId = $this->resolver->resolve($matchResult, $resolvedData, $this->channelContext);

        static::assertSame($this->layoutId3, $layoutId);
    }

    public function testNoLayoutReturnsNull(): void
    {
        // Don't create any layout assignments
        $route = $this->createRoute([
            ['entity' => 'product'],
            ['entity' => null],
        ]);

        $resolvedData = new ResolvedData(['product_id' => $this->productId], []);
        $matchResult = new RouteMatchResult($route, []);

        $layoutId = $this->resolver->resolve($matchResult, $resolvedData, $this->channelContext);

        static::assertNull($layoutId);
    }

    public function testCategoryFallbackWhenProductLayoutNotFound(): void
    {
        // Only create category layout (no product layout)
        $this->createLayoutAssignment('category', $this->categoryId, $this->layoutId2);
        $this->linkProductToCategory();

        $route = $this->createRoute([
            ['entity' => 'product'],
            ['via' => 'categories', 'entity' => 'category'],
            ['entity' => null],
        ]);

        $resolvedData = new ResolvedData(['product_id' => $this->productId], []);
        $matchResult = new RouteMatchResult($route, []);

        $layoutId = $this->resolver->resolve($matchResult, $resolvedData, $this->channelContext);

        // Should fall back to category layout
        static::assertSame($this->layoutId2, $layoutId);
    }

    private function createTestProduct(): void
    {
        $this->productRepository->create([
            [
                'id' => $this->productId,
                'productNumber' => 'TEST-' . $this->productId,
                'name' => 'Test Product',
                'stock' => 10,
                'price' => [
                    [
                        'currencyId' => Defaults::CURRENCY,
                        'gross' => 15,
                        'net' => 10,
                        'linked' => false,
                    ],
                ],
                'tax' => ['name' => 'test', 'taxRate' => 15],
            ],
        ], $this->context);
    }

    private function createTestLayouts(): void
    {
        // Layouts are created via migrations, just use IDs
        // In a real scenario, these would be created in the migration or setup
        $layouts = [
            ['id' => $this->layoutId1, 'name' => 'Test Layout 1'],
            ['id' => $this->layoutId2, 'name' => 'Test Layout 2'],
            ['id' => $this->layoutId3, 'name' => 'Test Layout 3'],
        ];

        foreach ($layouts as $layout) {
            $this->connection->insert('content_layout', [
                'id' => Uuid::fromHexToBytes($layout['id']),
                'name' => $layout['name'],
                'version' => '1.0',
                'structure' => json_encode([]),
                'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT),
            ]);
        }
    }

    private function createLayoutAssignment(?string $entityType, ?string $entityId, string $layoutId): void
    {
        $this->connection->insert('content_layout_assignment', [
            'id' => Uuid::fromHexToBytes(Uuid::randomHex()),
            'entity_type' => $entityType,
            'entity_id' => $entityId ? Uuid::fromHexToBytes($entityId) : null,
            'layout_id' => Uuid::fromHexToBytes($layoutId),
            'channel_id' => Uuid::fromHexToBytes($this->channelId),
            'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT),
        ]);
    }

    private function linkProductToCategory(): void
    {
        // Create category first
        $this->categoryRepository->create([
            [
                'id' => $this->categoryId,
                'name' => 'Test Category',
            ],
        ], $this->context);

        // Link product to category
        $this->connection->insert('product_category', [
            'product_id' => Uuid::fromHexToBytes($this->productId),
            'product_version_id' => Uuid::fromHexToBytes(Defaults::LIVE_VERSION),
            'category_id' => Uuid::fromHexToBytes($this->categoryId),
            'category_version_id' => Uuid::fromHexToBytes(Defaults::LIVE_VERSION),
        ]);
    }

    private function createRoute(array $layoutCascade): ContentRouteEntity
    {
        $route = new ContentRouteEntity();
        $route->setId(Uuid::randomHex());
        $route->setLayoutCascade($layoutCascade);

        return $route;
    }
}
