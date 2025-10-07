<?php declare(strict_types=1);

namespace HeyFrame\Tests\Integration\Core\Content\ContentSystem\Resolver;

use HeyFrame\Core\Content\ContentSystem\ContentRoute\ContentRouteEntity;
use HeyFrame\Core\Content\ContentSystem\Resolver\EntityIdResolver;
use HeyFrame\Core\Content\ContentSystem\Routing\Struct\RouteMatchResult;
use HeyFrame\Core\Content\Product\ProductCollection;
use HeyFrame\Core\Defaults;
use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityRepository;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Criteria;
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
#[CoversClass(EntityIdResolver::class)]
class EntityIdResolverTest extends TestCase
{
    use KernelTestBehaviour;

    private EntityIdResolver $resolver;

    private Context $context;

    private ChannelContext $channelContext;

    /**
     * @var EntityRepository<ProductCollection>
     */
    private EntityRepository $productRepository;

    private string $product1Id;

    private string $product2Id;

    private string $product3Id;

    private string $channelId;

    protected function setUp(): void
    {
        $this->resolver = static::getContainer()->get(EntityIdResolver::class);
        $this->context = Context::createDefaultContext();
        $this->productRepository = static::getContainer()->get('product.repository');

        $channelContextFactory = static::getContainer()->get(ChannelContextFactory::class);
        $this->channelContext = $channelContextFactory->create(Uuid::randomHex(), TestDefaults::CHANNEL);
        $this->channelId = $this->channelContext->getChannel()->getId();

        // Create test products with unique IDs - HeyFrame pattern uses Uuid::randomHex() for uniqueness
        $this->product1Id = Uuid::randomHex();
        $this->product2Id = Uuid::randomHex();
        $this->product3Id = Uuid::randomHex();

        $this->createTestProducts();
    }

    public function testSingleProductResolution(): void
    {
        // Get actual product number from created product
        $product = $this->productRepository->search(new Criteria([$this->product1Id]), $this->context)->first();
        static::assertNotNull($product);

        $route = $this->createRoute([
            'seoUrl' => [
                'placeholder' => 'product_id',
                'resolution' => [
                    'entity' => 'product',
                    'match_field' => 'productNumber',
                ],
            ],
        ]);

        $matchResult = new RouteMatchResult($route, ['seoUrl' => $product->getProductNumber()]);

        $resolved = $this->resolver->resolve($matchResult, $this->channelContext);

        static::assertNotNull($resolved);
        static::assertSame($this->product1Id, $resolved->getEntityId('product_id'));
    }

    public function testBatchProductResolution(): void
    {
        // Get actual product numbers from created products
        $product1 = $this->productRepository->search(new Criteria([$this->product1Id]), $this->context)->first();
        static::assertNotNull($product1);
        $product2 = $this->productRepository->search(new Criteria([$this->product2Id]), $this->context)->first();
        static::assertNotNull($product2);

        // This route expects multiple products in the URL
        $route = $this->createRoute([
            'product1' => [
                'placeholder' => 'product_id_1',
                'resolution' => [
                    'entity' => 'product',
                    'match_field' => 'productNumber',
                ],
            ],
            'product2' => [
                'placeholder' => 'product_id_2',
                'resolution' => [
                    'entity' => 'product',
                    'match_field' => 'productNumber',
                ],
            ],
        ]);

        $matchResult = new RouteMatchResult($route, [
            'product1' => $product1->getProductNumber(),
            'product2' => $product2->getProductNumber(),
        ]);

        $resolved = $this->resolver->resolve($matchResult, $this->channelContext);

        static::assertNotNull($resolved);
        static::assertSame($this->product1Id, $resolved->getEntityId('product_id_1'));
        static::assertSame($this->product2Id, $resolved->getEntityId('product_id_2'));
    }

    public function testConstraintsApplied(): void
    {
        $product1 = $this->productRepository->search(new Criteria([$this->product1Id]), $this->context)->first();
        static::assertNotNull($product1);
        $product2 = $this->productRepository->search(new Criteria([$this->product2Id]), $this->context)->first();
        static::assertNotNull($product2);

        $route = $this->createRoute([
            'productNumber' => [
                'placeholder' => 'product_id',
                'resolution' => [
                    'entity' => 'product',
                    'match_field' => 'productNumber',
                    'constraints' => [
                        'stock' => ['gte' => 100],
                    ],
                ],
            ],
        ]);

        // Product 1 has stock 50, Product 2 has stock 150
        $matchResult = new RouteMatchResult($route, ['productNumber' => $product1->getProductNumber()]);
        $resolved = $this->resolver->resolve($matchResult, $this->channelContext);

        // Should fail because stock is 50, not >= 100
        static::assertNull($resolved);

        // Product 2 should work
        $matchResult = new RouteMatchResult($route, ['productNumber' => $product2->getProductNumber()]);
        $resolved = $this->resolver->resolve($matchResult, $this->channelContext);

        static::assertNotNull($resolved);
        static::assertSame($this->product2Id, $resolved->getEntityId('product_id'));
    }

    public function testDifferentConstraintsPerItem(): void
    {
        $product1 = $this->productRepository->search(new Criteria([$this->product1Id]), $this->context)->first();
        static::assertNotNull($product1);
        $product2 = $this->productRepository->search(new Criteria([$this->product2Id]), $this->context)->first();
        static::assertNotNull($product2);

        $route = $this->createRoute([
            'product1' => [
                'placeholder' => 'product_id_1',
                'resolution' => [
                    'entity' => 'product',
                    'match_field' => 'productNumber',
                    'constraints' => [
                        'stock' => ['lt' => 100],
                    ],
                ],
            ],
            'product2' => [
                'placeholder' => 'product_id_2',
                'resolution' => [
                    'entity' => 'product',
                    'match_field' => 'productNumber',
                    'constraints' => [
                        'stock' => ['gte' => 100],
                    ],
                ],
            ],
        ]);

        $matchResult = new RouteMatchResult($route, [
            'product1' => $product1->getProductNumber(), // stock 50 < 100 ✓
            'product2' => $product2->getProductNumber(), // stock 150 >= 100 ✓
        ]);

        $resolved = $this->resolver->resolve($matchResult, $this->channelContext);

        static::assertNotNull($resolved);
        static::assertSame($this->product1Id, $resolved->getEntityId('product_id_1'));
        static::assertSame($this->product2Id, $resolved->getEntityId('product_id_2'));
    }

    public function testPassThroughParameters(): void
    {
        $product1 = $this->productRepository->search(new Criteria([$this->product1Id]), $this->context)->first();
        static::assertNotNull($product1);

        $route = $this->createRoute([
            'productNumber' => [
                'placeholder' => 'product_id',
                'resolution' => [
                    'entity' => 'product',
                    'match_field' => 'productNumber',
                ],
            ],
            'page' => [
                'placeholder' => 'page',
            ],
            'sort' => [
                'placeholder' => 'sort',
            ],
        ]);

        $matchResult = new RouteMatchResult($route, [
            'productNumber' => $product1->getProductNumber(),
            'page' => '2',
            'sort' => 'price-asc',
        ]);

        $resolved = $this->resolver->resolve($matchResult, $this->channelContext);

        static::assertNotNull($resolved);
        static::assertSame($this->product1Id, $resolved->getEntityId('product_id'));
        static::assertSame('2', $resolved->getParameter('page'));
        static::assertSame('price-asc', $resolved->getParameter('sort'));
    }

    public function testFailsWhenEntityNotFound(): void
    {
        $route = $this->createRoute([
            'productNumber' => [
                'placeholder' => 'product_id',
                'resolution' => [
                    'entity' => 'product',
                    'match_field' => 'productNumber',
                ],
            ],
        ]);

        $matchResult = new RouteMatchResult($route, ['productNumber' => 'NON-EXISTENT']);

        $resolved = $this->resolver->resolve($matchResult, $this->channelContext);

        static::assertNull($resolved);
    }

    public function testFailsWhenConstraintsNotSatisfied(): void
    {
        $product1 = $this->productRepository->search(new Criteria([$this->product1Id]), $this->context)->first();
        static::assertNotNull($product1);

        $route = $this->createRoute([
            'productNumber' => [
                'placeholder' => 'product_id',
                'resolution' => [
                    'entity' => 'product',
                    'match_field' => 'productNumber',
                    'constraints' => [
                        'active' => false,
                    ],
                ],
            ],
        ]);

        // All test products are active, so this should fail
        $matchResult = new RouteMatchResult($route, ['productNumber' => $product1->getProductNumber()]);

        $resolved = $this->resolver->resolve($matchResult, $this->channelContext);

        static::assertNull($resolved);
    }

    public function testNoResolutionParametersOnly(): void
    {
        $route = $this->createRoute([
            'page' => [
                'placeholder' => 'page',
            ],
            'limit' => [
                'placeholder' => 'limit',
            ],
        ]);

        $matchResult = new RouteMatchResult($route, [
            'page' => '1',
            'limit' => '24',
        ]);

        $resolved = $this->resolver->resolve($matchResult, $this->channelContext);

        static::assertNotNull($resolved);
        static::assertSame('1', $resolved->getParameter('page'));
        static::assertSame('24', $resolved->getParameter('limit'));
        static::assertEmpty($resolved->getEntityIds());
    }

    private function createTestProducts(): void
    {
        $this->productRepository->create([
            [
                'id' => $this->product1Id,
                'productNumber' => Uuid::randomHex(),
                'name' => 'Test Product 1',
                'stock' => 50,
                'active' => true,
                'price' => [
                    [
                        'currencyId' => Defaults::CURRENCY,
                        'gross' => 100,
                        'net' => 84,
                        'linked' => false,
                    ],
                ],
                'tax' => ['name' => 'test', 'taxRate' => 19],
                'visibilities' => [
                    [
                        'channelId' => $this->channelId,
                        'visibility' => 30,
                    ],
                ],
            ],
            [
                'id' => $this->product2Id,
                'productNumber' => Uuid::randomHex(),
                'name' => 'Test Product 2',
                'stock' => 150,
                'active' => true,
                'price' => [
                    [
                        'currencyId' => Defaults::CURRENCY,
                        'gross' => 200,
                        'net' => 168,
                        'linked' => false,
                    ],
                ],
                'tax' => ['name' => 'test', 'taxRate' => 19],
                'visibilities' => [
                    [
                        'channelId' => $this->channelId,
                        'visibility' => 30,
                    ],
                ],
            ],
            [
                'id' => $this->product3Id,
                'productNumber' => Uuid::randomHex(),
                'name' => 'Test Product 3',
                'stock' => 0,
                'active' => true,
                'price' => [
                    [
                        'currencyId' => Defaults::CURRENCY,
                        'gross' => 50,
                        'net' => 42,
                        'linked' => false,
                    ],
                ],
                'tax' => ['name' => 'test', 'taxRate' => 19],
                'visibilities' => [
                    [
                        'channelId' => $this->channelId,
                        'visibility' => 30,
                    ],
                ],
            ],
        ], $this->context);
    }

    /**
     * @param array<string, mixed> $parameterBinding
     */
    private function createRoute(array $parameterBinding): ContentRouteEntity
    {
        $route = new ContentRouteEntity();
        $route->setId(Uuid::randomHex());
        $route->setParameterBinding($parameterBinding);

        return $route;
    }
}
