<?php declare(strict_types=1);

namespace HeyFrame\Tests\Unit\Core\Maintenance\Channel\Command;

use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Uuid\Uuid;
use HeyFrame\Core\Maintenance\Channel\Command\ChannelListCommand;
use HeyFrame\Core\System\Channel\ChannelCollection;
use HeyFrame\Core\System\Channel\ChannelDefinition;
use HeyFrame\Core\System\Channel\ChannelEntity;
use HeyFrame\Core\Test\Stub\DataAbstractionLayer\StaticEntityRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * @internal
 */
#[Package('framework')]
#[CoversClass(ChannelListCommand::class)]
class ChannelListCommandTest extends TestCase
{
    public function testNoValidationErrors(): void
    {
        $id = Uuid::randomHex();

        $channel = new ChannelEntity();
        $channel->setUniqueIdentifier($id);
        $channel->setId($id);
        $channel->setAccessKey('123');
        $channel->setActive(true);
        $channel->setMaintenance(false);

        /** @var StaticEntityRepository<ChannelCollection> $channelRepository */
        $channelRepository = new StaticEntityRepository([new ChannelCollection([$channel])], new ChannelDefinition());

        $command = new ChannelListCommand($channelRepository);

        $commandTester = new CommandTester($command);
        $commandTester->execute([]);

        static::assertSame(
            0,
            $commandTester->getStatusCode(),
            "\"bin/console channel:list\" returned errors:\n" . $commandTester->getDisplay()
        );
        $output = '+----------------------------------+------+------------+------+--------+-------------+------------------+------------------+---------+
| id                               | Name | Access_key | Type | Active | Maintenance | Default Language | Default Currency | Domains |
+----------------------------------+------+------------+------+--------+-------------+------------------+------------------+---------+
| %s | n/a  | 123        | n/a  | active | off         | n/a              | n/a              |         |
+----------------------------------+------+------------+------+--------+-------------+------------------+------------------+---------+
';
        static::assertSame(\sprintf($output, $id), $commandTester->getDisplay());
    }
}
