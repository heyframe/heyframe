<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Test\Seo;

use HeyFrame\Core\Checkout\Cart\CartRuleLoader;
use HeyFrame\Core\Checkout\Customer\CustomerCollection;
use HeyFrame\Core\Checkout\Customer\CustomerEntity;
use HeyFrame\Core\Defaults;
use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityRepository;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Criteria;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use HeyFrame\Core\Framework\Test\TestCaseBase\KernelLifecycleManager;
use HeyFrame\Core\Framework\Uuid\Uuid;
use HeyFrame\Core\PlatformRequest;
use HeyFrame\Core\System\Channel\ChannelCollection;
use HeyFrame\Core\System\Channel\ChannelContext;
use HeyFrame\Core\System\Channel\ChannelEntity;
use HeyFrame\Core\System\Channel\Context\ChannelContextFactory;
use HeyFrame\Core\Test\TestDefaults;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\DependencyInjection\Container;

trait FrontendChannelTestHelper
{
    public function getBrowserWithLoggedInCustomer(): KernelBrowser
    {
        $browser = KernelLifecycleManager::createBrowser(KernelLifecycleManager::getKernel(), false);
        $browser->setServerParameters([
            'HTTP_ACCEPT' => 'application/json',
        ]);

        /** @var Container $container */
        $container = static::getContainer();

        /** @var EntityRepository<ChannelCollection> $channelRepository */
        $channelRepository = $container->get('channel.repository');
        $channel = $channelRepository->search(
            (new Criteria())->addFilter(new EqualsFilter('typeId', Defaults::CHANNEL_TYPE_STOREFRONT)),
            Context::createDefaultContext()
        )->getEntities()->first();
        TestCase::assertNotNull($channel);

        $header = 'HTTP_' . str_replace('-', '_', mb_strtoupper(PlatformRequest::HEADER_ACCESS_KEY));
        $browser->setServerParameter($header, $channel->getAccessKey());
        $browser->setServerParameter('test-sales-channel-id', $channel->getId());

        $customerId = Uuid::randomHex();
        $this->createCustomerWithEmail($customerId, 'foo@foo.de', 'bar12345', $channel);
        $browser->request(
            'POST',
            $_SERVER['APP_URL'] . '/account/login',
            [
                'username' => 'foo@foo.de',
                'password' => 'bar12345',
            ]
        );

        static::assertSame(200, $browser->getResponse()->getStatusCode());

        return $browser;
    }

    /**
     * @param array<string> $languageIds
     */
    public function createFrontendChannelContext(
        string $id,
        string $name,
        string $defaultLanguageId = Defaults::LANGUAGE_SYSTEM,
        array $languageIds = [],
        ?string $categoryEntrypoint = null
    ): ChannelContext {
        /** @var EntityRepository<ChannelCollection> $repo */
        $repo = static::getContainer()->get('channel.repository');
        $languageIds[] = $defaultLanguageId;
        $languageIds = array_unique($languageIds);

        $domains = [];
        $languages = [];

        $paymentMethod = $this->getValidPaymentMethodId();
        $shippingMethod = $this->getValidShippingMethodId();
        $country = $this->getValidCountryId(null);

        foreach ($languageIds as $langId) {
            $languages[] = ['id' => $langId];
            $domains[] = [
                'languageId' => $langId,
                'currencyId' => Defaults::CURRENCY,
                'snippetSetId' => $this->getSnippetSetIdForLocale('en-GB'),
                'url' => 'http://example.com/' . $name . '/' . $langId,
            ];
        }

        $repo->upsert([[
            'id' => $id,
            'name' => $name,
            'typeId' => Defaults::CHANNEL_TYPE_STOREFRONT,
            'accessKey' => Uuid::randomHex(),
            'secretAccessKey' => 'foobar',
            'languageId' => $defaultLanguageId,
            'snippetSetId' => $this->getSnippetSetIdForLocale('en-GB'),
            'currencyId' => Defaults::CURRENCY,
            'paymentMethodId' => $paymentMethod,
            'shippingMethodId' => $shippingMethod,
            'countryId' => $country,
            'currencies' => [['id' => Defaults::CURRENCY]],
            'languages' => $languages,
            'paymentMethods' => [['id' => $paymentMethod]],
            'shippingMethods' => [['id' => $shippingMethod]],
            'countries' => [['id' => $country]],
            'customerGroupId' => TestDefaults::FALLBACK_CUSTOMER_GROUP,
            'domains' => $domains,
            'navigationCategoryId' => !$categoryEntrypoint ? $this->getValidCategoryId() : $categoryEntrypoint,
        ]], Context::createDefaultContext());

        /** @var ChannelEntity $channel */
        $channel = $repo->search(new Criteria([$id]), Context::createDefaultContext())->first();

        return $this->createNewContext($channel);
    }

    public function updateChannelNavigationEntryPoint(string $id, string $categoryId): void
    {
        /** @var EntityRepository<ChannelCollection> $repo */
        $repo = static::getContainer()->get('channel.repository');

        $repo->update([['id' => $id, 'navigationCategoryId' => $categoryId]], Context::createDefaultContext());
    }

    private function createCustomerWithEmail(string $customerId, string $email, string $password, ChannelEntity $channel): CustomerEntity
    {
        /** @var Container $container */
        $container = static::getContainer();

        $defaultBillingAddress = Uuid::randomHex();

        $customer = [
            'id' => $customerId,
            'name' => 'test',
            'email' => $email,
            'password' => $password,
            'firstName' => 'foo',
            'lastName' => 'bar',
            'groupId' => $channel->getCustomerGroupId(),
            'salutationId' => $this->getValidSalutationId(),
            'channelId' => $channel->getId(),
            'defaultBillingAddress' => [
                'id' => $defaultBillingAddress,
                'countryId' => $channel->getCountryId(),
                'salutationId' => $this->getValidSalutationId(),
                'firstName' => 'foo',
                'lastName' => 'bar',
                'zipcode' => '48599',
                'city' => 'gronau',
                'street' => 'Schillerstr.',
            ],
            'defaultShippingAddressId' => $defaultBillingAddress,
            'customerNumber' => 'asdf',
        ];

        /** @var EntityRepository<CustomerCollection> */
        $customerRepository = $container->get('customer.repository');
        $customerRepository->upsert([$customer], Context::createDefaultContext());

        $customer = $customerRepository->search(new Criteria([$customerId]), Context::createDefaultContext())->first();

        static::assertInstanceOf(CustomerEntity::class, $customer);

        return $customer;
    }

    private function createNewContext(ChannelEntity $channel): ChannelContext
    {
        $factory = static::getContainer()->get(ChannelContextFactory::class);

        $context = $factory->create(Uuid::randomHex(), $channel->getId(), []);

        $ruleLoader = static::getContainer()->get(CartRuleLoader::class);
        $ruleLoader->loadByToken($context, $context->getToken());

        return $context;
    }
}
