<?php declare(strict_types=1);

namespace HeyFrame\Tests\Unit\Core\Framework\MessageQueue\ScheduledTask\Registry;

use HeyFrame\Core\Checkout\Cart\Cleanup\CleanupCartTask;
use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityRepository;
use HeyFrame\Core\Framework\DataAbstractionLayer\Event\EntityWrittenContainerEvent;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\EntitySearchResult;
use HeyFrame\Core\Framework\Event\NestedEventCollection;
use HeyFrame\Core\Framework\MessageQueue\ScheduledTask\Registry\TaskRegistry;
use HeyFrame\Core\Framework\MessageQueue\ScheduledTask\ScheduledTaskCollection;
use HeyFrame\Core\Framework\MessageQueue\ScheduledTask\ScheduledTaskDefinition;
use HeyFrame\Core\Framework\MessageQueue\ScheduledTask\ScheduledTaskEntity;
use HeyFrame\Core\Test\Stub\DataAbstractionLayer\StaticEntityRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;

/**
 * @internal
 */
#[CoversClass(TaskRegistry::class)]
class TaskRegistryTest extends TestCase
{
    /**
     * @var EntityRepository<ScheduledTaskCollection>&MockObject
     */
    private EntityRepository $scheduleTaskRepository;

    protected function setUp(): void
    {
        $this->scheduleTaskRepository = $this->createMock(EntityRepository::class);
    }

    public function testInvalidTasksAreDeleted(): void
    {
        $parameterBag = new ParameterBag([]);

        $registry = new TaskRegistry([], $this->scheduleTaskRepository, $parameterBag);

        $registeredTask = new ScheduledTaskEntity();

        $registeredTask->setId('deletedId');
        $registeredTask->setName(CleanupCartTask::getTaskName());
        $registeredTask->setRunInterval(CleanupCartTask::getDefaultInterval());
        $registeredTask->setDefaultRunInterval(CleanupCartTask::getDefaultInterval());
        $registeredTask->setStatus(ScheduledTaskDefinition::STATUS_SCHEDULED);
        $registeredTask->setNextExecutionTime(new \DateTimeImmutable());
        $registeredTask->setScheduledTaskClass('InvalidClass');
        $result = $this->createMock(EntitySearchResult::class);
        $result->method('getEntities')->willReturn(new ScheduledTaskCollection([$registeredTask]));
        $this->scheduleTaskRepository->expects($this->once())->method('search')->willReturn($result);
        $this->scheduleTaskRepository->expects($this->never())->method('update');
        $this->scheduleTaskRepository->expects($this->never())->method('create');
        $this->scheduleTaskRepository->expects($this->once())->method('delete')->with([
            [
                'id' => 'deletedId',
            ],
        ], Context::createDefaultContext());

        $registry->registerTasks();
    }

    public function testDefaultRunIntervalIsUpdatedIfItChanged(): void
    {
        $tasks = [new CleanupCartTask()];

        $registry = new TaskRegistry($tasks, $this->scheduleTaskRepository, new ParameterBag([]));

        $taskEntity = new ScheduledTaskEntity();
        $taskEntity->setId('cleanupTask');
        $taskEntity->setName(CleanupCartTask::getTaskName());
        $taskEntity->setRunInterval(10);
        $taskEntity->setDefaultRunInterval(20);
        $taskEntity->setStatus(ScheduledTaskDefinition::STATUS_SCHEDULED);
        $taskEntity->setNextExecutionTime(new \DateTimeImmutable());
        $taskEntity->setScheduledTaskClass(CleanupCartTask::class);

        $result = $this->createMock(EntitySearchResult::class);
        $result->method('getEntities')->willReturn(new ScheduledTaskCollection([$taskEntity]));

        $this->scheduleTaskRepository->expects($this->once())->method('search')->willReturn($result);

        $this->scheduleTaskRepository->expects($this->exactly(1))->method('update')->willReturnCallback(function (array $data, Context $context) {
            static::assertCount(1, $data);

            static::assertNotEmpty($data[0]);

            static::assertSame('cleanupTask', $data[0]['id']);
            static::assertSame(CleanupCartTask::getDefaultInterval(), $data[0]['defaultRunInterval']);
            static::assertArrayNotHasKey('runInterval', $data[0]);

            return new EntityWrittenContainerEvent($context, new NestedEventCollection(), []);
        });

        $this->scheduleTaskRepository->expects($this->never())->method('delete');
        $this->scheduleTaskRepository->expects($this->never())->method('create');

        $registry->registerTasks();
    }

    public function testRunIntervalIsUpdatedIfItMatchesDefault(): void
    {
        $tasks = [new CleanupCartTask()];

        $registry = new TaskRegistry($tasks, $this->scheduleTaskRepository, new ParameterBag([]));

        $taskEntity = new ScheduledTaskEntity();
        $taskEntity->setId('cleanupTask');
        $taskEntity->setName(CleanupCartTask::getTaskName());
        $taskEntity->setRunInterval(10);
        $taskEntity->setDefaultRunInterval(10);
        $taskEntity->setStatus(ScheduledTaskDefinition::STATUS_SCHEDULED);
        $taskEntity->setNextExecutionTime(new \DateTimeImmutable());
        $taskEntity->setScheduledTaskClass(CleanupCartTask::class);

        $result = $this->createMock(EntitySearchResult::class);
        $result->method('getEntities')->willReturn(new ScheduledTaskCollection([$taskEntity]));

        $this->scheduleTaskRepository->expects($this->once())->method('search')->willReturn($result);

        $this->scheduleTaskRepository->expects($this->exactly(1))->method('update')->willReturnCallback(function (array $data, Context $context) {
            static::assertCount(1, $data);

            static::assertNotEmpty($data[0]);

            static::assertSame('cleanupTask', $data[0]['id']);
            static::assertSame(CleanupCartTask::getDefaultInterval(), $data[0]['defaultRunInterval']);
            static::assertSame(CleanupCartTask::getDefaultInterval(), $data[0]['runInterval']);

            return new EntityWrittenContainerEvent($context, new NestedEventCollection(), []);
        });

        $this->scheduleTaskRepository->expects($this->never())->method('delete');
        $this->scheduleTaskRepository->expects($this->never())->method('create');

        $registry->registerTasks();
    }

    public function testListAllTasks(): void
    {
        $taskEntity = new ScheduledTaskEntity();
        $taskEntity->setId('cleanupTask');
        $taskEntity->setName('foo');

        /** @var StaticEntityRepository<ScheduledTaskCollection> $repository */
        $repository = new StaticEntityRepository([new ScheduledTaskCollection([$taskEntity])]);

        $tasks = (new TaskRegistry([], $repository, new ParameterBag([])))->getAllTasks(Context::createDefaultContext());

        static::assertCount(1, $tasks);
        static::assertSame($taskEntity, $tasks->first());
    }

    public function testScheduleTaskSuccessfully(): void
    {
        $taskEntity = new ScheduledTaskEntity();
        $taskEntity->setId('test-task-id');
        $taskEntity->setName('test.task');
        $taskEntity->setStatus(ScheduledTaskDefinition::STATUS_SCHEDULED);

        $result = $this->createMock(EntitySearchResult::class);
        $result->method('getEntities')->willReturnOnConsecutiveCalls(
            new ScheduledTaskCollection([$taskEntity]),
            new ScheduledTaskCollection([$taskEntity])
        );
        $result->method('first')->willReturn($taskEntity);

        $this->scheduleTaskRepository->expects($this->exactly(2))
            ->method('search')
            ->willReturn($result);

        $this->scheduleTaskRepository->expects($this->once())
            ->method('update')
            ->with(
                [[
                    'id' => 'test-task-id',
                    'status' => ScheduledTaskDefinition::STATUS_SCHEDULED,
                ]],
                static::isInstanceOf(Context::class)
            );

        $registry = new TaskRegistry([], $this->scheduleTaskRepository, new ParameterBag([]));
        $status = $registry->scheduleTask('test.task', false, false, Context::createDefaultContext());

        static::assertSame(ScheduledTaskDefinition::STATUS_SCHEDULED, $status);
    }

    public function testScheduleTaskWithImmediatelyOption(): void
    {
        $taskEntity = new ScheduledTaskEntity();
        $taskEntity->setId('test-task-id');
        $taskEntity->setName('test.task');
        $taskEntity->setStatus(ScheduledTaskDefinition::STATUS_SCHEDULED);

        $result = $this->createMock(EntitySearchResult::class);
        $result->method('getEntities')->willReturnOnConsecutiveCalls(
            new ScheduledTaskCollection([$taskEntity]),
            new ScheduledTaskCollection([$taskEntity])
        );
        $result->method('first')->willReturn($taskEntity);

        $this->scheduleTaskRepository->expects($this->exactly(2))
            ->method('search')
            ->willReturn($result);

        $this->scheduleTaskRepository->expects($this->once())
            ->method('update')
            ->with(
                static::callback(function (array $data) {
                    static::assertCount(1, $data);
                    static::assertSame('test-task-id', $data[0]['id']);
                    static::assertSame(ScheduledTaskDefinition::STATUS_SCHEDULED, $data[0]['status']);
                    static::assertInstanceOf(\DateTimeImmutable::class, $data[0]['nextExecutionTime']);

                    return true;
                }),
                static::isInstanceOf(Context::class)
            );

        $registry = new TaskRegistry([], $this->scheduleTaskRepository, new ParameterBag([]));
        $status = $registry->scheduleTask('test.task', true, false, Context::createDefaultContext());

        static::assertSame(ScheduledTaskDefinition::STATUS_SCHEDULED, $status);
    }

    public function testScheduleTaskFailsWhenRunningWithoutForce(): void
    {
        $taskEntity = new ScheduledTaskEntity();
        $taskEntity->setId('test-task-id');
        $taskEntity->setName('test.task');
        $taskEntity->setStatus(ScheduledTaskDefinition::STATUS_RUNNING);

        $result = $this->createMock(EntitySearchResult::class);
        $result->method('getEntities')->willReturn(new ScheduledTaskCollection([$taskEntity]));
        $result->method('first')->willReturn($taskEntity);

        $this->scheduleTaskRepository->expects($this->once())
            ->method('search')
            ->willReturn($result);

        $this->scheduleTaskRepository->expects($this->never())
            ->method('update');

        $registry = new TaskRegistry([], $this->scheduleTaskRepository, new ParameterBag([]));
        $status = $registry->scheduleTask('test.task', false, false, Context::createDefaultContext());

        static::assertSame(ScheduledTaskDefinition::STATUS_RUNNING, $status);
    }

    public function testScheduleTaskFailsWhenQueuedWithoutForce(): void
    {
        $taskEntity = new ScheduledTaskEntity();
        $taskEntity->setId('test-task-id');
        $taskEntity->setName('test.task');
        $taskEntity->setStatus(ScheduledTaskDefinition::STATUS_QUEUED);

        $result = $this->createMock(EntitySearchResult::class);
        $result->method('getEntities')->willReturn(new ScheduledTaskCollection([$taskEntity]));
        $result->method('first')->willReturn($taskEntity);

        $this->scheduleTaskRepository->expects($this->once())
            ->method('search')
            ->willReturn($result);

        $this->scheduleTaskRepository->expects($this->never())
            ->method('update');

        $registry = new TaskRegistry([], $this->scheduleTaskRepository, new ParameterBag([]));
        $status = $registry->scheduleTask('test.task', false, false, Context::createDefaultContext());

        static::assertSame(ScheduledTaskDefinition::STATUS_QUEUED, $status);
    }

    public function testScheduleTaskSucceedsWhenRunningWithForce(): void
    {
        $taskEntity = new ScheduledTaskEntity();
        $taskEntity->setId('test-task-id');
        $taskEntity->setName('test.task');
        $taskEntity->setStatus(ScheduledTaskDefinition::STATUS_RUNNING);

        $result = $this->createMock(EntitySearchResult::class);
        $result->method('getEntities')->willReturnOnConsecutiveCalls(
            new ScheduledTaskCollection([$taskEntity]),
            new ScheduledTaskCollection([$taskEntity])
        );
        $result->method('first')->willReturn($taskEntity);

        $this->scheduleTaskRepository->expects($this->exactly(2))
            ->method('search')
            ->willReturn($result);

        $this->scheduleTaskRepository->expects($this->once())
            ->method('update')
            ->with(
                [[
                    'id' => 'test-task-id',
                    'status' => ScheduledTaskDefinition::STATUS_SCHEDULED,
                ]],
                static::isInstanceOf(Context::class)
            );

        $registry = new TaskRegistry([], $this->scheduleTaskRepository, new ParameterBag([]));
        $status = $registry->scheduleTask('test.task', false, true, Context::createDefaultContext());

        static::assertSame(ScheduledTaskDefinition::STATUS_RUNNING, $status);
    }

    public function testScheduleTaskThrowsExceptionWhenTaskNotFound(): void
    {
        $result = $this->createMock(EntitySearchResult::class);
        $result->method('getEntities')->willReturn(new ScheduledTaskCollection([]));
        $result->method('first')->willReturn(null);

        $this->scheduleTaskRepository->expects($this->once())
            ->method('search')
            ->willReturn($result);

        $registry = new TaskRegistry([], $this->scheduleTaskRepository, new ParameterBag([]));

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Tried to fetch "non.existing.task" scheduled task, but scheduled task does not exist');

        $registry->scheduleTask('non.existing.task', false, false, Context::createDefaultContext());
    }

    public function testDeactivateTaskSuccessfully(): void
    {
        $taskEntity = new ScheduledTaskEntity();
        $taskEntity->setId('test-task-id');
        $taskEntity->setName('test.task');
        $taskEntity->setStatus(ScheduledTaskDefinition::STATUS_INACTIVE);

        $result = $this->createMock(EntitySearchResult::class);
        $result->method('getEntities')->willReturnOnConsecutiveCalls(
            new ScheduledTaskCollection([$taskEntity]),
            new ScheduledTaskCollection([$taskEntity])
        );
        $result->method('first')->willReturn($taskEntity);

        $this->scheduleTaskRepository->expects($this->exactly(2))
            ->method('search')
            ->willReturn($result);

        $this->scheduleTaskRepository->expects($this->once())
            ->method('update')
            ->with(
                [[
                    'id' => 'test-task-id',
                    'status' => ScheduledTaskDefinition::STATUS_INACTIVE,
                ]],
                static::isInstanceOf(Context::class)
            );

        $registry = new TaskRegistry([], $this->scheduleTaskRepository, new ParameterBag([]));
        $status = $registry->deactivateTask('test.task', false, Context::createDefaultContext());

        static::assertSame(ScheduledTaskDefinition::STATUS_INACTIVE, $status);
    }

    public function testDeactivateTaskFailsWhenRunningWithoutForce(): void
    {
        $taskEntity = new ScheduledTaskEntity();
        $taskEntity->setId('test-task-id');
        $taskEntity->setName('test.task');
        $taskEntity->setStatus(ScheduledTaskDefinition::STATUS_RUNNING);

        $result = $this->createMock(EntitySearchResult::class);
        $result->method('getEntities')->willReturn(new ScheduledTaskCollection([$taskEntity]));
        $result->method('first')->willReturn($taskEntity);

        $this->scheduleTaskRepository->expects($this->once())
            ->method('search')
            ->willReturn($result);

        $this->scheduleTaskRepository->expects($this->never())
            ->method('update');

        $registry = new TaskRegistry([], $this->scheduleTaskRepository, new ParameterBag([]));
        $status = $registry->deactivateTask('test.task', false, Context::createDefaultContext());

        static::assertSame(ScheduledTaskDefinition::STATUS_RUNNING, $status);
    }

    public function testDeactivateTaskFailsWhenQueuedWithoutForce(): void
    {
        $taskEntity = new ScheduledTaskEntity();
        $taskEntity->setId('test-task-id');
        $taskEntity->setName('test.task');
        $taskEntity->setStatus(ScheduledTaskDefinition::STATUS_QUEUED);

        $result = $this->createMock(EntitySearchResult::class);
        $result->method('getEntities')->willReturn(new ScheduledTaskCollection([$taskEntity]));
        $result->method('first')->willReturn($taskEntity);

        $this->scheduleTaskRepository->expects($this->once())
            ->method('search')
            ->willReturn($result);

        $this->scheduleTaskRepository->expects($this->never())
            ->method('update');

        $registry = new TaskRegistry([], $this->scheduleTaskRepository, new ParameterBag([]));
        $status = $registry->deactivateTask('test.task', false, Context::createDefaultContext());

        static::assertSame(ScheduledTaskDefinition::STATUS_QUEUED, $status);
    }

    public function testDeactivateTaskSucceedsWhenRunningWithForce(): void
    {
        $taskEntity = new ScheduledTaskEntity();
        $taskEntity->setId('test-task-id');
        $taskEntity->setName('test.task');
        $taskEntity->setStatus(ScheduledTaskDefinition::STATUS_RUNNING);

        $result = $this->createMock(EntitySearchResult::class);
        $result->method('getEntities')->willReturnOnConsecutiveCalls(
            new ScheduledTaskCollection([$taskEntity]),
            new ScheduledTaskCollection([$taskEntity])
        );
        $result->method('first')->willReturn($taskEntity);

        $this->scheduleTaskRepository->expects($this->exactly(2))
            ->method('search')
            ->willReturn($result);

        $this->scheduleTaskRepository->expects($this->once())
            ->method('update')
            ->with(
                [[
                    'id' => 'test-task-id',
                    'status' => ScheduledTaskDefinition::STATUS_INACTIVE,
                ]],
                static::isInstanceOf(Context::class)
            );

        $registry = new TaskRegistry([], $this->scheduleTaskRepository, new ParameterBag([]));
        $status = $registry->deactivateTask('test.task', true, Context::createDefaultContext());

        static::assertSame(ScheduledTaskDefinition::STATUS_RUNNING, $status);
    }

    public function testDeactivateTaskThrowsExceptionWhenTaskNotFound(): void
    {
        $result = $this->createMock(EntitySearchResult::class);
        $result->method('getEntities')->willReturn(new ScheduledTaskCollection([]));
        $result->method('first')->willReturn(null);

        $this->scheduleTaskRepository->expects($this->once())
            ->method('search')
            ->willReturn($result);

        $registry = new TaskRegistry([], $this->scheduleTaskRepository, new ParameterBag([]));

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Tried to fetch "non.existing.task" scheduled task, but scheduled task does not exist');

        $registry->deactivateTask('non.existing.task', false, Context::createDefaultContext());
    }
}
