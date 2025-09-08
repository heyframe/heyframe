<?php declare(strict_types=1);

namespace HeyFrame\Tests\Integration\Core\Framework\MessageQueue\ScheduledTask;

use HeyFrame\Core\Framework\MessageQueue\Command\RegisterScheduledTasksCommand;
use HeyFrame\Core\Framework\MessageQueue\ScheduledTask\Registry\TaskRegistry;
use HeyFrame\Core\Framework\Test\TestCaseBase\IntegrationTestBehaviour;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * @internal
 */
class RegisterScheduledTaskTest extends TestCase
{
    use IntegrationTestBehaviour;

    public function testNoValidationErrors(): void
    {
        $taskRegistry = $this->createMock(TaskRegistry::class);
        $taskRegistry->expects($this->once())
            ->method('registerTasks');

        $commandTester = new CommandTester(new RegisterScheduledTasksCommand($taskRegistry));
        $commandTester->execute([]);
    }
}
