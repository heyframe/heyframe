<?php declare(strict_types=1);

namespace HeyFrame\Tests\Integration\Core\Maintenance\Channel\Service;

use HeyFrame\Core\Defaults;
use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityRepository;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Criteria;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Test\TestCaseBase\IntegrationTestBehaviour;
use HeyFrame\Core\Framework\Uuid\Uuid;
use HeyFrame\Core\Maintenance\Channel\Service\ChannelCreator;
use HeyFrame\Core\System\Channel\ChannelCollection;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[Package('framework')]
class ChannelCreatorTest extends TestCase
{
    use IntegrationTestBehaviour;

    private ChannelCreator $channelCreator;

    /**
     * @var EntityRepository<ChannelCollection>
     */
    private EntityRepository $channelRepository;

    protected function setUp(): void
    {
        $this->channelCreator = static::getContainer()->get(ChannelCreator::class);
        $this->channelRepository = static::getContainer()->get('channel.repository');
    }

    public function testCreateChannel(): void
    {
        $id = Uuid::randomHex();
        $this->channelCreator->createChannel($id, 'test', Defaults::CHANNEL_TYPE_API);

        $channel = $this->channelRepository->search(new Criteria([$id]), Context::createDefaultContext())->getEntities()->first();

        static::assertNotNull($channel);
        static::assertSame('test', $channel->getName());
        static::assertSame(Defaults::CHANNEL_TYPE_API, $channel->getTypeId());
    }
}
