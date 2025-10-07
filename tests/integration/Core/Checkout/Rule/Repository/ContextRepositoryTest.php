<?php declare(strict_types=1);

namespace HeyFrame\Tests\Integration\Core\Checkout\Rule\Repository;

use HeyFrame\Core\Content\Rule\RuleCollection;
use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityRepository;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Criteria;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Rule\Container\AndRule;
use HeyFrame\Core\Framework\Rule\Container\OrRule;
use HeyFrame\Core\Framework\Test\TestCaseBase\DatabaseTransactionBehaviour;
use HeyFrame\Core\Framework\Test\TestCaseBase\KernelTestBehaviour;
use HeyFrame\Core\Framework\Uuid\Uuid;
use HeyFrame\Core\System\Currency\Rule\CurrencyRule;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[Package('fundamentals@after-sales')]
class ContextRepositoryTest extends TestCase
{
    use DatabaseTransactionBehaviour;
    use KernelTestBehaviour;

    /**
     * @var EntityRepository<RuleCollection>
     */
    private EntityRepository $repository;

    private Context $context;

    protected function setUp(): void
    {
        $this->repository = static::getContainer()->get('rule.repository');
        $this->context = Context::createDefaultContext();
    }

    public function testWriteRuleWithObject(): void
    {
        $id = Uuid::randomHex();
        $currencyId = Uuid::randomHex();
        $currencyId2 = Uuid::randomHex();

        $data = [
            'id' => $id,
            'name' => 'test rule',
            'priority' => 1,
            'conditions' => [
                [
                    'type' => (new AndRule())->getName(),
                    'children' => [
                        [
                            'type' => (new OrRule())->getName(),
                            'children' => [
                                [
                                    'type' => (new CurrencyRule())->getName(),
                                    'value' => [
                                        'currencyIds' => [
                                            $currencyId,
                                            $currencyId2,
                                        ],
                                        'operator' => CurrencyRule::OPERATOR_EQ,
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $this->repository->create([$data], $this->context);

        $rules = $this->repository->search(new Criteria([$id]), $this->context)->getEntities();

        $entity = $rules->get($id);
        static::assertNotNull($entity);
    }
}
