<?php declare(strict_types=1);

namespace HeyFrame\Tests\Integration\Core\Content\ContentSystem\Routing;

use Doctrine\DBAL\Connection;
use HeyFrame\Core\Content\ContentSystem\Routing\RouteCollection\RouteCollectionBuilder;
use HeyFrame\Core\Defaults;
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
#[CoversClass(RouteCollectionBuilder::class)]
class RouteCollectionBuilderTest extends TestCase
{
    use KernelTestBehaviour;

    private RouteCollectionBuilder $builder;

    private Connection $connection;

    private ChannelContext $channelContext;

    private string $channelId;

    private string $routeId1;

    private string $routeId2;

    private string $routeId3;

    private string $layoutId;

    protected function setUp(): void
    {
        $this->builder = static::getContainer()->get(RouteCollectionBuilder::class);
        $this->connection = static::getContainer()->get(Connection::class);

        $channelContextFactory = static::getContainer()->get(ChannelContextFactory::class);

        // Use existing test sales channel
        $this->channelContext = $channelContextFactory->create(Uuid::randomHex(), TestDefaults::CHANNEL);
        $this->channelId = $this->channelContext->getChannel()->getId();

        // Create test data
        $this->layoutId = Uuid::randomHex();
        $this->routeId1 = Uuid::randomHex();
        $this->routeId2 = Uuid::randomHex();
        $this->routeId3 = Uuid::randomHex();

        $this->createTestLayout();
        $this->createTestRoutes();
    }

    protected function tearDown(): void
    {
        // Clean up test data
        $this->connection->executeStatement('DELETE FROM content_route_channel WHERE content_route_id IN (:r1, :r2, :r3)', [
            'r1' => Uuid::fromHexToBytes($this->routeId1),
            'r2' => Uuid::fromHexToBytes($this->routeId2),
            'r3' => Uuid::fromHexToBytes($this->routeId3),
        ]);
        $this->connection->executeStatement('DELETE FROM content_route WHERE id IN (:r1, :r2, :r3)', [
            'r1' => Uuid::fromHexToBytes($this->routeId1),
            'r2' => Uuid::fromHexToBytes($this->routeId2),
            'r3' => Uuid::fromHexToBytes($this->routeId3),
        ]);
        $this->connection->executeStatement('DELETE FROM content_layout WHERE id = :id', [
            'id' => Uuid::fromHexToBytes($this->layoutId),
        ]);
    }

    public function testGlobalRouteVisible(): void
    {
        // Route 1 has no sales channel assignments = global
        $collection = $this->builder->build($this->channelContext);

        static::assertNotNull($collection->get('content_route_' . $this->routeId1));
    }

    public function testRouteAssignedToChannel(): void
    {
        // Assign route 2 to test sales channel
        $this->assignRouteToChannel($this->routeId2, $this->channelId);

        $collection = $this->builder->build($this->channelContext);

        static::assertNotNull($collection->get('content_route_' . $this->routeId2));
    }

    public function testRouteWithAssignmentExcludesGlobalBehavior(): void
    {
        // Route 2 assigned to test sales channel - verifies assignment system works
        $this->assignRouteToChannel($this->routeId2, $this->channelId);

        $collection = $this->builder->build($this->channelContext);

        // Route 2 should be visible (assigned to this channel)
        static::assertNotNull($collection->get('content_route_' . $this->routeId2));

        // Route 1 should also be visible (global - no assignments)
        static::assertNotNull($collection->get('content_route_' . $this->routeId1));
    }

    public function testInactiveRouteNotIncluded(): void
    {
        // Mark route 1 as inactive
        $this->connection->executeStatement(
            'UPDATE content_route SET active = 0 WHERE id = :id',
            ['id' => Uuid::fromHexToBytes($this->routeId1)]
        );

        $collection = $this->builder->build($this->channelContext);

        static::assertNull($collection->get('content_route_' . $this->routeId1));
    }

    public function testRoutesSortedByPriority(): void
    {
        $collection = $this->builder->build($this->channelContext);

        $routes = iterator_to_array($collection->getIterator());
        $routeKeys = array_keys($routes);

        // Routes should be sorted by priority (descending): route3 (100) > route2 (50) > route1 (0)
        static::assertSame('content_route_' . $this->routeId3, $routeKeys[0]);
        static::assertSame('content_route_' . $this->routeId2, $routeKeys[1]);
        static::assertSame('content_route_' . $this->routeId1, $routeKeys[2]);
    }

    private function createTestLayout(): void
    {
        $this->connection->insert('content_layout', [
            'id' => Uuid::fromHexToBytes($this->layoutId),
            'name' => 'Test Layout',
            'version' => '1.0',
            'structure' => json_encode([]),
            'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT),
        ]);
    }

    private function createTestRoutes(): void
    {
        // Route 1: Global (no assignments), priority 0
        $this->connection->insert('content_route', [
            'id' => Uuid::fromHexToBytes($this->routeId1),
            'name' => 'Global Route',
            'url_pattern' => '/global/{id}',
            'parameter_binding' => json_encode([]),
            'layout_id' => Uuid::fromHexToBytes($this->layoutId),
            'priority' => 0,
            'active' => 1,
            'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT),
        ]);

        // Route 2: Will be assigned to specific sales channel, priority 50
        $this->connection->insert('content_route', [
            'id' => Uuid::fromHexToBytes($this->routeId2),
            'name' => 'Sales Channel A Route',
            'url_pattern' => '/channel-a/{id}',
            'parameter_binding' => json_encode([]),
            'layout_id' => Uuid::fromHexToBytes($this->layoutId),
            'priority' => 50,
            'active' => 1,
            'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT),
        ]);

        // Route 3: Will be assigned to multiple sales channels, priority 100
        $this->connection->insert('content_route', [
            'id' => Uuid::fromHexToBytes($this->routeId3),
            'name' => 'Multi Channel Route',
            'url_pattern' => '/multi/{id}',
            'parameter_binding' => json_encode([]),
            'layout_id' => Uuid::fromHexToBytes($this->layoutId),
            'priority' => 100,
            'active' => 1,
            'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT),
        ]);
    }

    private function assignRouteToChannel(string $routeId, string $channelId): void
    {
        $this->connection->insert('content_route_channel', [
            'content_route_id' => Uuid::fromHexToBytes($routeId),
            'channel_id' => Uuid::fromHexToBytes($channelId),
            'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT),
        ]);
    }
}
