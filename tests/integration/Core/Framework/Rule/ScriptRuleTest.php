<?php declare(strict_types=1);

namespace HeyFrame\Tests\Integration\Core\Framework\Rule;

use HeyFrame\Core\Checkout\CheckoutRuleScope;
use HeyFrame\Core\Checkout\Customer\CustomerEntity;
use HeyFrame\Core\Content\Rule\Aggregate\RuleCondition\RuleConditionCollection;
use HeyFrame\Core\Content\Rule\RuleCollection;
use HeyFrame\Core\Content\Rule\RuleEntity;
use HeyFrame\Core\Framework\App\Aggregate\AppScriptCondition\AppScriptConditionCollection;
use HeyFrame\Core\Framework\App\Aggregate\AppScriptCondition\AppScriptConditionEntity;
use HeyFrame\Core\Framework\App\AppCollection;
use HeyFrame\Core\Framework\App\AppEntity;
use HeyFrame\Core\Framework\App\AppStateService;
use HeyFrame\Core\Framework\App\Lifecycle\AbstractAppLifecycle;
use HeyFrame\Core\Framework\App\Lifecycle\AppLifecycle;
use HeyFrame\Core\Framework\App\Lifecycle\Parameters\AppInstallParameters;
use HeyFrame\Core\Framework\App\Manifest\Manifest;
use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityRepository;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Criteria;
use HeyFrame\Core\Framework\DataAbstractionLayer\Write\WriteException;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Rule\Container\AndRule;
use HeyFrame\Core\Framework\Rule\Rule;
use HeyFrame\Core\Framework\Rule\ScriptRule;
use HeyFrame\Core\Framework\Test\TestCaseBase\DatabaseTransactionBehaviour;
use HeyFrame\Core\Framework\Test\TestCaseBase\KernelTestBehaviour;
use HeyFrame\Core\Framework\Uuid\Uuid;
use HeyFrame\Core\System\Channel\ChannelContext;
use HeyFrame\Core\System\Channel\Context\ChannelContextFactory;
use HeyFrame\Core\Test\TestDefaults;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * @internal
 */
#[Package('fundamentals@after-sales')]
#[RunTestsInSeparateProcesses]
class ScriptRuleTest extends TestCase
{
    use DatabaseTransactionBehaviour;
    use KernelTestBehaviour;

    /**
     * @var EntityRepository<RuleCollection>
     */
    private EntityRepository $ruleRepository;

    /**
     * @var EntityRepository<RuleConditionCollection>
     */
    private EntityRepository $conditionRepository;

    /**
     * @var EntityRepository<AppCollection>
     */
    private EntityRepository $appRepository;

    private AppStateService $appStateService;

    private AbstractAppLifecycle $appLifecycle;

    private Context $context;

    private string $scriptId;

    private string $appId;

    protected function setUp(): void
    {
        $this->ruleRepository = static::getContainer()->get('rule.repository');
        $this->conditionRepository = static::getContainer()->get('rule_condition.repository');
        $this->appRepository = static::getContainer()->get('app.repository');
        $this->appStateService = static::getContainer()->get(AppStateService::class);
        $this->appLifecycle = static::getContainer()->get(AppLifecycle::class);
        $this->context = Context::createDefaultContext();
    }

    /**
     * @param array<string, string> $values
     */
    #[DataProvider('scriptProvider')]
    public function testRuleScriptExecution(string $path, array $values, bool $expectedTrue): void
    {
        $script = file_get_contents(__DIR__ . $path);
        $scope = new CheckoutRuleScope($this->createChannelContext());
        $rule = new ScriptRule();

        $rule->assign([
            'values' => $values,
            'script' => $script,
            'debug' => false,
            'cacheDir' => static::getContainer()->getParameter('kernel.cache_dir'),
        ]);

        if ($expectedTrue) {
            static::assertTrue($rule->match($scope));
        } else {
            static::assertFalse($rule->match($scope));
        }
    }

    public static function scriptProvider(): \Generator
    {
        yield 'simple script return true' => ['/_fixture/scripts/simple.twig', ['test' => 'foo'], true];
        yield 'simple script return false' => ['/_fixture/scripts/simple.twig', ['test' => 'bar'], false];
    }

    #[Depends('testRuleScriptExecution')]
    public function testRuleScriptIsCached(): void
    {
        $channelContext = $this->createMock(ChannelContext::class);
        $scope = new CheckoutRuleScope($channelContext);
        $rule = new ScriptRule();

        $rule->assign([
            'script' => '{% return true %}',
            'values' => [],
            'lastModified' => (new \DateTimeImmutable())->sub(new \DateInterval('P1D')),
            'debug' => false,
            'cacheDir' => static::getContainer()->getParameter('kernel.cache_dir'),
        ]);

        static::assertFalse($rule->match($scope));
    }

    #[Depends('testRuleScriptIsCached')]
    public function testCachedRuleScriptIsInvalidated(): void
    {
        $channelContext = $this->createMock(ChannelContext::class);
        $scope = new CheckoutRuleScope($channelContext);
        $rule = new ScriptRule();

        $rule->assign([
            'script' => '{% return true %}',
            'values' => [],
            'debug' => false,
            'cacheDir' => static::getContainer()->getParameter('kernel.cache_dir'),
        ]);

        static::assertTrue($rule->match($scope));
    }

    public function testRuleValidationFails(): void
    {
        $this->installApp();

        try {
            $ruleId = Uuid::randomHex();
            $this->ruleRepository->create(
                [['id' => $ruleId, 'name' => 'Demo rule', 'priority' => 1]],
                Context::createDefaultContext()
            );

            $id = Uuid::randomHex();
            $this->conditionRepository->create([
                [
                    'id' => $id,
                    'type' => (new ScriptRule())->getName(),
                    'ruleId' => $ruleId,
                    'scriptId' => $this->scriptId,
                    'value' => [
                        'operator' => 'foo',
                    ],
                ],
            ], $this->context);

            static::fail('Exception was not thrown');
        } catch (WriteException $stackException) {
            $exceptions = iterator_to_array($stackException->getErrors(), false);
            static::assertCount(2, $exceptions);
            static::assertSame('/0/value/operator', $exceptions[0]['source']['pointer']);
            static::assertSame(Choice::NO_SUCH_CHOICE_ERROR, $exceptions[0]['code']);
            static::assertSame('/0/value/customerGroupIds', $exceptions[1]['source']['pointer']);
            static::assertSame(NotBlank::IS_BLANK_ERROR, $exceptions[1]['code']);
        }
    }

    public static function manifestPathProvider(): \Generator
    {
        yield 'Default fixture App with customerGroupIds property' => [
            '/test/manifest.xml',
            [
                'operator' => '=',
                'customerGroupIds' => [Uuid::randomHex()],
            ],
        ];

        yield 'App with name as rule property' => [
            '/test/manifest_arbitraryRule_name.xml',
            [
                'name' => 'hello',
                'operator' => '=',
            ],
        ];

        yield 'App with existing constraints name as rule property' => [
            '/test/manifest_arbitraryRule_constraints.xml',
            [
                'operator' => '=',
                'constraints' => 'broken',
            ],
        ];
    }

    /**
     * @param array<string, mixed> $value
     */
    #[DataProvider('manifestPathProvider')]
    public function testRuleValidationSucceedsWithArbitraryProperties(string $manifestPath, array $value): void
    {
        $fixturesPath = __DIR__ . '/../App/Manifest/_fixtures';
        $manifest = Manifest::createFromXmlFile($fixturesPath . $manifestPath);
        $this->setupApp($manifest);

        $ruleId = Uuid::randomHex();
        $this->ruleRepository->create(
            [['id' => $ruleId, 'name' => 'Demo rule', 'priority' => 1]],
            $this->context
        );

        $id = Uuid::randomHex();
        $this->conditionRepository->create([
            [
                'id' => $id,
                'type' => (new ScriptRule())->getName(),
                'ruleId' => $ruleId,
                'scriptId' => $this->scriptId,
                'value' => $value,
            ],
        ], $this->context);

        $rule = $this->ruleRepository->search(new Criteria([$ruleId]), $this->context)->getEntities()->get($ruleId);
        static::assertInstanceOf(RuleEntity::class, $rule);
        $payload = $rule->getPayload();
        static::assertInstanceOf(AndRule::class, $payload);

        $scriptRule = $payload->getRules()[0];
        static::assertInstanceOf(ScriptRule::class, $scriptRule);
        static::assertSame($value, $scriptRule->getValues());
        static::assertSame([], $scriptRule->getConstraints());

        $this->ruleRepository->delete([['id' => $ruleId]], $this->context);
        $this->conditionRepository->delete([['id' => $id]], $this->context);
    }

    public function testRuleValueAssignment(): void
    {
        $rule = new ScriptRule();
        $value = [
            'operator' => '=',
            'customerGroupIds' => [Uuid::randomHex()],
        ];
        $rule->assignValues($value);

        static::assertSame($value, $rule->getValues());
    }

    private function getCheckoutScope(string $ruleId, string $conditionId): CheckoutRuleScope
    {
        $this->ruleRepository->create(
            [['id' => $ruleId, 'name' => 'Demo rule', 'priority' => 1]],
            $this->context
        );

        $groupId = Uuid::randomHex();
        $this->conditionRepository->create([
            [
                'id' => $conditionId,
                'type' => (new ScriptRule())->getName(),
                'ruleId' => $ruleId,
                'scriptId' => $this->scriptId,
                'value' => [
                    'customerGroupIds' => [Uuid::randomHex(), $groupId],
                    'operator' => Rule::OPERATOR_EQ,
                ],
            ],
        ], $this->context);

        $channelContext = $this->createMock(ChannelContext::class);
        $customer = new CustomerEntity();

        $customer->setGroupId($groupId);
        $channelContext->method('getCustomer')->willReturn($customer);

        return new CheckoutRuleScope($channelContext);
    }

    private function installApp(): void
    {
        $fixturesPath = __DIR__ . '/../App/Manifest/_fixtures';

        $manifest = Manifest::createFromXmlFile($fixturesPath . '/test/manifest.xml');
        $this->setupApp($manifest);
    }

    private function setupApp(Manifest $manifest): void
    {
        $this->appLifecycle->install($manifest, new AppInstallParameters(activate: false), $this->context);

        $app = $this->appRepository->search((new Criteria())->addAssociation('scriptConditions'), $this->context)->first();
        static::assertInstanceOf(AppEntity::class, $app);
        $this->appId = $app->getId();
        $this->appStateService->activateApp($this->appId, $this->context);
        $conditions = $app->getScriptConditions();
        static::assertInstanceOf(AppScriptConditionCollection::class, $conditions);
        $condition = $conditions->first();
        static::assertInstanceOf(AppScriptConditionEntity::class, $condition);
        $this->scriptId = $condition->getId();
    }

    private function createChannelContext(): ChannelContext
    {
        $channelContextFactory = static::getContainer()->get(ChannelContextFactory::class);

        return $channelContextFactory->create(Uuid::randomHex(), TestDefaults::CHANNEL);
    }
}
