<?php declare(strict_types=1);

namespace HeyFrame\Tests\Integration\Administration\Controller;

use Doctrine\DBAL\Connection;
use HeyFrame\Core\Checkout\Customer\CustomerCollection;
use HeyFrame\Core\Defaults;
use HeyFrame\Core\Framework\Api\Util\AccessKeyHelper;
use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityRepository;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Criteria;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use HeyFrame\Core\Framework\Test\TestCaseBase\AdminFunctionalTestBehaviour;
use HeyFrame\Core\Framework\Uuid\Uuid;
use HeyFrame\Core\System\SystemConfig\SystemConfigService;
use HeyFrame\Core\Test\TestDefaults;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class AdministrationControllerTest extends TestCase
{
    use AdminFunctionalTestBehaviour;

    private Connection $connection;

    /**
     * @var EntityRepository<CustomerCollection>
     */
    private EntityRepository $customerRepository;

    protected function setUp(): void
    {
        $this->connection = static::getContainer()->get(Connection::class);

        $this->customerRepository = static::getContainer()->get('customer.repository');
    }

    public function testSnippetRoute(): void
    {
        $this->getBrowser()->request('GET', '/api/_admin/snippets?locale=en-GB');
        static::assertSame(200, $this->getBrowser()->getResponse()->getStatusCode());
        $content = $this->getBrowser()->getResponse()->getContent();
        static::assertNotFalse($content);

        $response = json_decode($content, true, 512, \JSON_THROW_ON_ERROR);
        static::assertIsArray($response);
        static::assertArrayHasKey('zh-CN', $response);
        static::assertArrayHasKey('en-GB', $response);
    }

    public function testResetExcludedSearchTermIncorrectLanguageId(): void
    {
        $this->getBrowser()->setServerParameter('HTTP_sw-language-id', Uuid::randomHex());
        $this->getBrowser()->request('POST', '/api/_admin/reset-excluded-search-term');

        $response = $this->getBrowser()->getResponse();

        static::assertSame(412, $response->getStatusCode());
    }

    public function testValidateEmailSuccess(): void
    {
        $browser = $this->createClient();
        $this->createCustomer(['email' => 'foo@bar.de']);

        $browser->request(
            'POST',
            '/api/_admin/check-customer-email-valid',
            [
                'id' => Uuid::randomHex(),
                'email' => 'foo1@bar.de',
                'boundChannelId' => null,
            ]
        );

        $content = $this->getBrowser()->getResponse()->getContent();
        static::assertNotFalse($content);

        $response = json_decode($content, true, 512, \JSON_THROW_ON_ERROR);
        static::assertSame(200, $browser->getResponse()->getStatusCode());
        static::assertArrayHasKey('isValid', $response);
    }

    public function testValidateEmailFail(): void
    {
        $email = 'foo@bar.de';
        $browser = $this->createClient();
        $this->createCustomer(['email' => 'foo@bar.de']);

        $browser->request(
            'POST',
            '/api/_admin/check-customer-email-valid',
            [
                'id' => Uuid::randomHex(),
                'email' => $email,
                'boundChannelId' => null,
            ]
        );

        $content = $this->getBrowser()->getResponse()->getContent();
        static::assertNotFalse($content);

        $response = json_decode($content, true, 512, \JSON_THROW_ON_ERROR);
        static::assertSame(400, $browser->getResponse()->getStatusCode());
        static::assertSame('The email address ' . $email . ' is already in use', $response['errors'][0]['detail']);
    }

    public function testValidateEmailSuccessWithSameCustomerDifferentChannel(): void
    {
        $this->setCustomerBoundToChannels(true);
        $newChannel = $this->createChannel();

        $browser = $this->createClient();
        $email = 'foo@bar.de';
        $this->createCustomer(['email' => 'foo@bar.de', 'boundChannelId' => TestDefaults::CHANNEL]);

        $browser->request(
            'POST',
            '/api/_admin/check-customer-email-valid',
            [
                'id' => Uuid::randomHex(),
                'email' => $email,
                'boundChannelId' => $newChannel['id'],
            ]
        );

        $content = $this->getBrowser()->getResponse()->getContent();
        static::assertNotFalse($content);

        $response = json_decode($content, true, 512, \JSON_THROW_ON_ERROR);
        static::assertSame(200, $browser->getResponse()->getStatusCode());
        static::assertArrayHasKey('isValid', $response);
    }

    public function testValidateEmailFailWithSameCustomerSameChannel(): void
    {
        $this->setCustomerBoundToChannels(true);
        $email = 'foo@bar.de';
        $browser = $this->createClient();
        $this->createCustomer(['email' => 'foo@bar.de', 'boundChannelId' => TestDefaults::CHANNEL]);

        $browser->request(
            'POST',
            '/api/_admin/check-customer-email-valid',
            [
                'id' => Uuid::randomHex(),
                'email' => $email,
                'boundChannelId' => TestDefaults::CHANNEL,
            ]
        );

        $content = $this->getBrowser()->getResponse()->getContent();
        static::assertNotFalse($content);

        $response = json_decode($content, true, 512, \JSON_THROW_ON_ERROR);
        static::assertSame(400, $browser->getResponse()->getStatusCode());
        static::assertSame('The email address ' . $email . ' is already in use in the Sales Channel 小程序', $response['errors'][0]['detail']);
    }

    public function testValidateEmailFailWithSameCustomerIsAlreadyExistsInAllChannel(): void
    {
        $this->setCustomerBoundToChannels(true);
        $email = 'foo@bar.de';
        $browser = $this->createClient();
        $this->createCustomer(['email' => $email]);

        $browser->request(
            'POST',
            '/api/_admin/check-customer-email-valid',
            [
                'id' => Uuid::randomHex(),
                'email' => $email,
                'boundChannelId' => TestDefaults::CHANNEL,
            ]
        );

        $content = $this->getBrowser()->getResponse()->getContent();
        static::assertNotFalse($content);

        $response = json_decode($content, true, 512, \JSON_THROW_ON_ERROR);
        static::assertSame(400, $browser->getResponse()->getStatusCode());
        static::assertSame('The email address ' . $email . ' is already in use', $response['errors'][0]['detail']);
    }

    /**
     * @param array<string, string|bool|int|float|null> $overrideData
     */
    private function createCustomer(array $overrideData): string
    {
        $customerId = Uuid::randomHex();

        $customer = array_merge([
            'id' => $customerId,
            'channelId' => TestDefaults::CHANNEL,
            'groupId' => TestDefaults::FALLBACK_CUSTOMER_GROUP,
            'email' => 'random@mail.com',
            'password' => TestDefaults::HASHED_PASSWORD,
            'nickname' => 'Mustermann',
            'customerNumber' => '12345',
        ], $overrideData);

        $this->customerRepository->create([$customer], Context::createDefaultContext());

        return $customerId;
    }

    private function setCustomerBoundToChannels(bool $value): void
    {
        static::getContainer()
            ->get(SystemConfigService::class)
            ->set('core.systemWideLoginRegistration.isCustomerBoundToChannel', $value);
    }

    /**
     * @param array<string, int|float|string|bool|null> $channelOverride
     *
     * @return array<string, array<int, array<string, string|null>>|bool|float|int|string|null>
     */
    private function createChannel(array $channelOverride = []): array
    {
        $channelRepository = static::getContainer()->get('channel.repository');
        $paymentMethod = $this->getAvailablePaymentMethod();

        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('domains.url', 'http://localhost'));
        $channelIds = $channelRepository->searchIds($criteria, Context::createDefaultContext());

        if (!isset($channelOverride['domains']) && $channelIds->firstId() !== null) {
            $channelRepository->delete([['id' => $channelIds->firstId()]], Context::createDefaultContext());
        }

        $channel = array_merge([
            'id' => $channelOverride['id'] ?? Uuid::randomHex(),
            'typeId' => Defaults::CHANNEL_TYPE_FRONTEND,
            'name' => 'new sales channel',
            'accessKey' => AccessKeyHelper::generateAccessKey('channel'),
            'languageId' => Defaults::LANGUAGE_SYSTEM,
            'snippetSetId' => $this->getSnippetSetIdForLocale('en-GB'),
            'currencyId' => Defaults::CURRENCY,
            'paymentMethodId' => $paymentMethod->getId(),
            'paymentMethods' => [['id' => $paymentMethod->getId()]],
            'navigationCategoryId' => $this->getValidCategoryId(),
            'countryId' => $this->getValidCountryId(null),
            'currencies' => [['id' => Defaults::CURRENCY]],
            'languages' => $channelOverride['languages'] ?? [['id' => Defaults::LANGUAGE_SYSTEM]],
            'customerGroupId' => TestDefaults::FALLBACK_CUSTOMER_GROUP,
            'domains' => [
                [
                    'languageId' => Defaults::LANGUAGE_SYSTEM,
                    'currencyId' => Defaults::CURRENCY,
                    'snippetSetId' => $this->getSnippetSetIdForLocale('zh-CN'),
                    'url' => 'http://localhost',
                ],
            ],
            'countries' => [['id' => $this->getValidCountryId(null)]],
        ], $channelOverride);

        $channelRepository->upsert([$channel], Context::createDefaultContext());

        return $channel;
    }
}
