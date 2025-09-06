<?php declare(strict_types=1);

namespace HeyFrame\Tests\Integration\Core\Content\Flow;

use HeyFrame\Core\Checkout\Customer\CustomerEntity;
use HeyFrame\Core\Checkout\Customer\Event\CustomerLoginEvent;
use HeyFrame\Core\Content\Flow\Dispatching\Action\SetCustomerCustomFieldAction;
use HeyFrame\Core\Content\Flow\FlowCollection;
use HeyFrame\Core\Content\Test\Flow\OrderActionTrait;
use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityRepository;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Criteria;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Test\TestCaseBase\CacheTestBehaviour;
use HeyFrame\Core\Framework\Uuid\Uuid;
use HeyFrame\Core\Test\Stub\Framework\IdsCollection;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[Package('after-sales')]
class SetCustomerCustomFieldActionTest extends TestCase
{
    use CacheTestBehaviour;
    use OrderActionTrait;

    /**
     * @var EntityRepository<FlowCollection>
     */
    private EntityRepository $flowRepository;

    protected function setUp(): void
    {
        $this->flowRepository = static::getContainer()->get('flow.repository');

        $this->customerRepository = static::getContainer()->get('customer.repository');

        $this->ids = new IdsCollection();

        $this->browser = $this->createCustomChannelBrowser([
            'id' => $this->ids->create('channel'),
        ]);

        $this->browser->setServerParameter('HTTP_SW_CONTEXT_TOKEN', $this->ids->create('token'));
    }

    /**
     * @param array<int, mixed>|null $existedData
     * @param array<int, mixed>|null $updateData
     * @param array<int, mixed>|null $expectData
     */
    #[DataProvider('createDataProvider')]
    public function testCreateCustomFieldForCustomer(string $option, ?array $existedData, ?array $updateData, ?array $expectData): void
    {
        $customFieldName = 'custom_field_test';
        $entity = 'customer';
        $customFieldId = $this->createCustomField($customFieldName, $entity);

        $email = 'thuy@gmail.com';
        $this->prepareCustomer($email, ['customFields' => [$customFieldName => $existedData]]);

        $sequenceId = Uuid::randomHex();
        $this->flowRepository->create([[
            'name' => 'Customer login',
            'eventName' => CustomerLoginEvent::EVENT_NAME,
            'priority' => 1,
            'active' => true,
            'sequences' => [
                [
                    'id' => $sequenceId,
                    'parentId' => null,
                    'ruleId' => null,
                    'actionName' => SetCustomerCustomFieldAction::getName(),
                    'position' => 1,
                    'config' => [
                        'entity' => $entity,
                        'customFieldId' => $customFieldId,
                        'customFieldText' => $customFieldName,
                        'customFieldValue' => $updateData,
                        'customFieldSetId' => null,
                        'customFieldSetText' => null,
                        'option' => $option,
                    ],
                ],
            ],
        ]], Context::createDefaultContext());

        $this->login($email, 'heyframe');

        static::assertNotNull($this->customerRepository);
        /** @var CustomerEntity $customer */
        $customer = $this->customerRepository->search(new Criteria([$this->ids->get('customer')]), Context::createDefaultContext())->first();

        $expect = $option === 'clear' ? null : [$customFieldName => $expectData];
        static::assertSame($customer->getCustomFields(), $expect);
    }

    /**
     * @return array<string, mixed>
     */
    public static function createDataProvider(): array
    {
        return [
            'upsert / existed data / update data / expect data' => ['upsert', ['red', 'green'], ['blue', 'gray'], ['blue', 'gray']],
            'create / existed data / update data / expect data' => ['create', ['red', 'green'], ['blue', 'gray'], ['red', 'green']],
            'clear / existed data / update data / expect data' => ['clear', ['red', 'green', 'blue'], null, null],
            'add / existed data / update data / expect data' => ['add', ['red', 'green'], ['blue', 'gray'], ['red', 'green', 'blue', 'gray']],
            'remove / existed data / update data / expect data' => ['remove', ['red', 'green', 'blue'], ['green', 'blue'], ['red']],
        ];
    }
}
