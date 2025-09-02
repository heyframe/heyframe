<?php declare(strict_types=1);

namespace HeyFrame\Tests\Integration\Core\Checkout\Customer\Channel;

use HeyFrame\Core\Checkout\Customer\Channel\AccountService;
use HeyFrame\Core\Checkout\Customer\CustomerEntity;
use HeyFrame\Core\Checkout\Customer\Exception\BadCredentialsException;
use HeyFrame\Core\Checkout\Customer\Exception\CustomerNotFoundException;
use HeyFrame\Core\Checkout\Customer\Exception\PasswordPoliciesUpdatedException;
use HeyFrame\Core\Defaults;
use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Criteria;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Test\TestCaseBase\ChannelFunctionalTestBehaviour;
use HeyFrame\Core\Framework\Util\Hasher;
use HeyFrame\Core\Framework\Uuid\Uuid;
use HeyFrame\Core\System\Channel\Context\ChannelContextService;
use HeyFrame\Core\System\Channel\Context\ChannelContextServiceParameters;
use HeyFrame\Core\Test\TestDefaults;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[Package('checkout')]
class AccountServiceTest extends TestCase
{
    use ChannelFunctionalTestBehaviour;

    private AccountService $accountService;

    protected function setUp(): void
    {
        $this->accountService = static::getContainer()->get(AccountService::class);
    }

    public function testLoginById(): void
    {
        $channelContext = $this->createChannelContext();
        $customerId = $this->createCustomerOfChannel($channelContext->getChannelId(), 'foo@bar.com');
        $token = $this->accountService->loginById($customerId, $channelContext);

        $customer = $this->getCustomerFromToken($token, $channelContext->getChannelId());

        static::assertSame('foo@bar.com', $customer->getEmail());
        static::assertSame($customerId, $customer->getId());
    }

    public function testLoginByCredentials(): void
    {
        $channelContext = $this->createChannelContext();
        $customerId = $this->createCustomerOfChannel($channelContext->getChannelId(), 'foo@bar.com');
        $token = $this->accountService->loginByCredentials('foo@bar.com', 'heyframe', $channelContext);

        $customer = $this->getCustomerFromToken($token, $channelContext->getChannelId());

        static::assertSame('foo@bar.com', $customer->getEmail());
        static::assertSame($customerId, $customer->getId());
    }

    public function testGetCustomerByLogin(): void
    {
        $email = 'johndoe@example.com';

        $context = $this->createChannelContext([
            'domains' => [
                [
                    'url' => 'https://test.de',
                    'currencyId' => Defaults::CURRENCY,
                    'languageId' => Defaults::LANGUAGE_SYSTEM,
                    'snippetSetId' => $this->getRandomId('snippet_set'),
                ],
            ],
        ]);
        $this->createCustomerOfChannel($context->getChannelId(), $email);

        $customer = $this->accountService->getCustomerByLogin($email, 'heyframe', $context);
        static::assertSame($email, $customer->getEmail());
        static::assertSame($context->getChannelId(), $customer->getChannelId());
    }

    public function testGetCustomerByLoginWithInvalidPassword(): void
    {
        $this->expectException(BadCredentialsException::class);

        $email = 'johndoe@example.com';

        $context = $this->createChannelContext([
            'domains' => [
                [
                    'url' => 'https://test.de',
                    'currencyId' => Defaults::CURRENCY,
                    'languageId' => Defaults::LANGUAGE_SYSTEM,
                    'snippetSetId' => $this->getRandomId('snippet_set'),
                ],
            ],
        ]);
        $this->createCustomerOfChannel($context->getChannelId(), $email);

        $customer = $this->accountService->getCustomerByLogin($email, 'invalid-password', $context);
        static::assertSame($email, $customer->getEmail());
        static::assertSame($context->getChannelId(), $customer->getChannelId());
    }

    public function testGetCustomerByLoginWhenCustomersHaveSameEmailReturnsTheLatestCreatedCustomer(): void
    {
        $idCustomer1 = Uuid::randomHex();
        $idCustomer2 = Uuid::randomHex();
        $email = 'johndoe@example.com';
        $context = $this->createChannelContext([
            'domains' => [
                [
                    'url' => 'https://test.de',
                    'currencyId' => Defaults::CURRENCY,
                    'languageId' => Defaults::LANGUAGE_SYSTEM,
                    'snippetSetId' => $this->getRandomId('snippet_set'),
                ],
            ],
        ]);

        $this->createCustomerOfChannel($context->getChannelId(), $email, true, true, $idCustomer1, '2022-10-21 10:00:00');
        $this->createCustomerOfChannel($context->getChannelId(), $email, true, true, $idCustomer2, '2022-10-22 10:00:00');

        $customer = $this->accountService->getCustomerByLogin($email, 'heyframe', $context);
        static::assertSame($idCustomer2, $customer->getId());
    }

    public function testGetCustomerByLoginWhenCustomersInDifferentChannelsHaveSameEmail(): void
    {
        $email = 'johndoe@example.com';

        $context1 = $this->createChannelContext([
            'domains' => [
                [
                    'url' => 'https://test.de',
                    'currencyId' => Defaults::CURRENCY,
                    'languageId' => Defaults::LANGUAGE_SYSTEM,
                    'snippetSetId' => $this->getRandomId('snippet_set'),
                ],
            ],
        ]);
        $this->createCustomerOfChannel($context1->getChannelId(), $email);

        $context2 = $this->createChannelContext([
            'domains' => [
                [
                    'url' => 'http://test.en',
                    'currencyId' => Defaults::CURRENCY,
                    'languageId' => Defaults::LANGUAGE_SYSTEM,
                    'snippetSetId' => $this->getRandomId('snippet_set'),
                ],
            ],
        ]);

        $this->createCustomerOfChannel($context2->getChannelId(), $email);

        $customer1 = $this->accountService->getCustomerByLogin($email, 'heyframe', $context1);

        static::assertSame($context1->getChannelId(), $customer1->getChannelId());

        $customer2 = $this->accountService->getCustomerByLogin($email, 'heyframe', $context2);
        static::assertSame($context2->getChannelId(), $customer2->getChannelId());
    }

    public function testCustomerFailsToLoginByMailWithInactiveAccount(): void
    {
        $email = 'johndoe@example.com';

        $context = $this->createChannelContext([
            'domains' => [
                [
                    'url' => 'https://test.de',
                    'currencyId' => Defaults::CURRENCY,
                    'languageId' => Defaults::LANGUAGE_SYSTEM,
                    'snippetSetId' => $this->getRandomId('snippet_set'),
                ],
            ],
        ]);
        $this->createCustomerOfChannel($context->getChannelId(), $email, true, false);

        $this->expectException(CustomerNotFoundException::class);
        $this->expectExceptionMessage('No matching customer for the email "johndoe@example.com" was found.');
        $this->accountService->getCustomerByLogin($email, 'heyframe', $context);
    }

    public function testGetCustomerByLoginLegacyPasswordIsUpdatedToNewOne(): void
    {
        $idCustomer = Uuid::randomHex();
        $email = 'johndoe@example.com';

        $context = $this->createChannelContext([
            'domains' => [
                [
                    'url' => 'http://test.de',
                    'currencyId' => Defaults::CURRENCY,
                    'languageId' => Defaults::LANGUAGE_SYSTEM,
                    'snippetSetId' => $this->getRandomId('snippet_set'),
                ],
            ],
        ]);
        $this->createCustomerOfChannel($context->getChannelId(), $email, true, true, $idCustomer, '2022-10-21 10:00:00', Hasher::hash('heyframe', 'md5'), 'Md5');

        $customer = $this->accountService->getCustomerByLogin($email, 'heyframe', $context);
        static::assertSame($email, $customer->getEmail());
        static::assertSame($context->getChannelId(), $customer->getChannelId());

        $customer = $this
            ->getContainer()
            ->get('customer.repository')
            ->search(new Criteria([$idCustomer]), $context->getContext())
            ->first();
        static::assertInstanceOf(CustomerEntity::class, $customer);
        static::assertNull($customer->getLegacyPassword());
        static::assertNull($customer->getLegacyEncoder());
        static::assertNotNull($customer->getPassword());
    }

    public function testCustomerFailsToLoginByLegacyPasswordWithOutdatedPasswordPolicy(): void
    {
        $idCustomer = Uuid::randomHex();
        $email = 'johndoe@example.com';

        $context = $this->createChannelContext([
            'domains' => [
                [
                    'url' => 'http://test.de',
                    'currencyId' => Defaults::CURRENCY,
                    'languageId' => Defaults::LANGUAGE_SYSTEM,
                    'snippetSetId' => $this->getRandomId('snippet_set'),
                ],
            ],
        ]);
        $this->createCustomerOfChannel($context->getChannelId(), $email, true, true, $idCustomer, '2022-10-21 10:00:00', Hasher::hash('test', 'md5'), 'Md5');

        static::expectException(PasswordPoliciesUpdatedException::class);
        static::expectExceptionMessage('Password policies updated.');
        $this->accountService->getCustomerByLogin($email, 'test', $context);
    }

    private function getCustomerFromToken(string $contextToken, string $channelId): CustomerEntity
    {
        $channelContextService = static::getContainer()->get(ChannelContextService::class);
        $context = $channelContextService->get(
            new ChannelContextServiceParameters($channelId, $contextToken)
        );

        $customer = $context->getCustomer();
        static::assertNotNull($customer);

        return $customer;
    }

    private function createCustomerOfChannel(
        string $channelId,
        string $email,
        bool $boundToChannel = true,
        bool $active = true,
        ?string $customerId = null,
        ?string $createdAt = null,
        ?string $password = TestDefaults::HASHED_PASSWORD,
        ?string $legacyEncoder = null
    ): string {
        $customerId ??= Uuid::randomHex();
        $customer = [
            'id' => $customerId,
            'createdAt' => $createdAt,
            'number' => '1337',
            'nickname' => 'Mustermann123',
            'customerNumber' => '1337',
            'email' => $email,
            'password' => $legacyEncoder ? null : $password,
            'legacyEncoder' => $legacyEncoder,
            'legacyPassword' => $legacyEncoder ? $password : null,
            'boundChannelId' => $boundToChannel ? $channelId : null,
            'groupId' => TestDefaults::FALLBACK_CUSTOMER_GROUP,
            'channelId' => $channelId,
            'active' => $active,
        ];

        static::getContainer()
            ->get('customer.repository')
            ->upsert([$customer], Context::createDefaultContext());

        return $customerId;
    }
}
