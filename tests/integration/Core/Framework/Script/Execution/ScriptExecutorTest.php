<?php declare(strict_types=1);

namespace HeyFrame\Tests\Integration\Core\Framework\Script\Execution;

use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\DataAbstractionLayer\Facade\RepositoryFacadeHookFactory;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\EntitySearchResult;
use HeyFrame\Core\Framework\Script\Exception\ScriptExecutionFailedException;
use HeyFrame\Core\Framework\Script\Execution\ScriptExecutor;
use HeyFrame\Core\Framework\Script\ScriptException;
use HeyFrame\Core\Framework\Struct\ArrayStruct;
use HeyFrame\Core\Framework\Test\Script\Execution\DeprecatedTestHook;
use HeyFrame\Core\Framework\Test\Script\Execution\FunctionWillBeRequiredTestHook;
use HeyFrame\Core\Framework\Test\Script\Execution\StoppableTestHook;
use HeyFrame\Core\Framework\Test\Script\Execution\TestHook;
use HeyFrame\Core\Framework\Test\TestCaseBase\IntegrationTestBehaviour;
use HeyFrame\Core\Test\AppSystemTestBehaviour;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Constraint\Constraint;
use PHPUnit\Framework\Constraint\IsInstanceOf;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class ScriptExecutorTest extends TestCase
{
    use AppSystemTestBehaviour;
    use IntegrationTestBehaviour;

    private ScriptExecutor $executor;

    protected function setUp(): void
    {
        $this->executor = static::getContainer()->get(ScriptExecutor::class);
    }

    /**
     * @param array<string> $hooks
     * @param array<string, mixed> $expected
     */
    #[DataProvider('executeProvider')]
    public function testExecute(array $hooks, array $expected): void
    {
        $this->loadAppsFromDir(__DIR__ . '/_fixtures');

        $object = new ArrayStruct();

        $context = Context::createDefaultContext();
        foreach ($hooks as $hook) {
            $this->executor->execute(new TestHook($hook, $context, ['object' => $object], [RepositoryFacadeHookFactory::class]));
        }

        static::assertNotEmpty($expected);

        foreach ($expected as $key => $value) {
            static::assertTrue($object->has($key));
            if ($value instanceof Constraint) {
                static::assertThat($object->get($key), $value);

                continue;
            }
            static::assertSame($value, $object->get($key));
        }
    }

    public function testExecuteGetHeyFrameVersion(): void
    {
        $this->testExecute(
            ['heyframe-version-case'],
            ['version' => static::getContainer()->getParameter('kernel.heyframe_version'), 'version_compare' => true]
        );
    }

    public function testNoneExistingServicesRequired(): void
    {
        $this->loadAppsFromDir(__DIR__ . '/_fixtures');

        $this->expectException(ScriptExecutionFailedException::class);
        $this->expectExceptionMessage('The service "Hook: simple-function-case" has a dependency on a non-existent service "none-existing"');

        $this->executor->execute(new TestHook('simple-function-case', Context::createDefaultContext(), [], ['none-existing']));
    }

    public function testHookAwareServiceValidation(): void
    {
        $this->loadAppsFromDir(__DIR__ . '/_fixtures');

        $this->expectException(ScriptExecutionFailedException::class);
        $innerException = ScriptException::noHookServiceFactory('product.repository');

        $this->expectExceptionMessage($innerException->getMessage());

        $this->executor->execute(new TestHook('simple-function-case', Context::createDefaultContext(), [], ['product.repository']));
    }

    public function testStoppableHooksStopsPropagation(): void
    {
        $this->loadAppsFromDir(__DIR__ . '/_fixtures');

        $object = new ArrayStruct();

        $context = Context::createDefaultContext();
        $this->executor->execute(new StoppableTestHook('stoppable-case', $context, ['object' => $object]));

        static::assertSame([
            'first-script' => 'called',
            'second-script' => 'called',
        ], $object->all());
    }

    public function testExecuteDeprecatedHookTriggersDeprecation(): void
    {
        $this->loadAppsFromDir(__DIR__ . '/_fixtures');

        $object = new ArrayStruct();

        $context = Context::createDefaultContext();
        $this->executor->execute(new DeprecatedTestHook('simple-function-case', $context, ['object' => $object]));

        static::assertTrue($object->has('foo'));
        static::assertSame('bar', $object->get('foo'));

        $traces = $this->getScriptTraces();
        static::assertArrayHasKey('simple-function-case', $traces);
        static::assertCount(1, $traces['simple-function-case'][0]['deprecations']);
        static::assertSame([
            DeprecatedTestHook::getDeprecationNotice() => 1,
        ], $traces['simple-function-case'][0]['deprecations']);
    }

    public function testAccessDeprecatedServiceOfHookTriggersDeprecation(): void
    {
        $this->loadAppsFromDir(__DIR__ . '/_fixtures');

        $context = Context::createDefaultContext();
        $this->executor->execute(new TestHook(
            'simple-service-script',
            $context,
            [],
            [RepositoryFacadeHookFactory::class],
            [RepositoryFacadeHookFactory::class => 'The `repository` service is deprecated for testing purposes.']
        ));

        $traces = $this->getScriptTraces();
        static::assertArrayHasKey('simple-service-script', $traces);
        static::assertArrayHasKey('The `repository` service is deprecated for testing purposes.', $traces['simple-service-script'][0]['deprecations']);
        static::assertSame(2, $traces['simple-service-script'][0]['deprecations']['The `repository` service is deprecated for testing purposes.']);
    }

    public function testNotImplementingAFunctionThatWillBeRequiredTriggersException(): void
    {
        $this->loadAppsFromDir(__DIR__ . '/_fixtures');

        $object = new ArrayStruct();

        $context = Context::createDefaultContext();
        $this->executor->execute(new FunctionWillBeRequiredTestHook(
            'simple-function-case',
            $context,
            ['object' => $object],
        ));

        $traces = $this->getScriptTraces();
        static::assertArrayHasKey('simple-function-case::test', $traces);
        static::assertCount(1, $traces['simple-function-case::test'][0]['deprecations']);
        static::assertSame([
            'Function "test" will be required from v6.5.0.0 onward, but is not implemented in script "simple-function-case/simple-function-case.twig", please make sure you add the block in your script.' => 1,
        ], $traces['simple-function-case::test'][0]['deprecations']);
    }

    /**
     * @return array<string, array{0: array<string>, 1: array<string, mixed>}>
     */
    public static function executeProvider(): iterable
    {
        yield 'Test simple function call' => [
            ['simple-function-case'],
            ['foo' => 'bar'],
        ];
        yield 'Test multiple scripts called' => [
            ['simple-function-case', 'multi-script-case'],
            ['foo' => 'bar', 'bar' => 'foo', 'baz' => 'foo'],
        ];
        yield 'Test include with function call' => [
            ['include-case'],
            ['called' => 1],
        ];
        yield 'Test include with function call and complex return type' => [
            ['include-with-complex-return-type-case'],
            ['return' => new IsInstanceOf(EntitySearchResult::class)],
        ];
    }
}
