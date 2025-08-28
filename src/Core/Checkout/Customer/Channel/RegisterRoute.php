<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Customer\Channel;

use Doctrine\DBAL\Connection;
use HeyFrame\Core\Checkout\Customer\Aggregate\CustomerAddress\CustomerAddressDefinition;
use HeyFrame\Core\Checkout\Customer\CustomerCollection;
use HeyFrame\Core\Checkout\Customer\CustomerDefinition;
use HeyFrame\Core\Checkout\Customer\CustomerEntity;
use HeyFrame\Core\Checkout\Customer\CustomerEvents;
use HeyFrame\Core\Checkout\Customer\CustomerException;
use HeyFrame\Core\Checkout\Customer\Event\CustomerConfirmRegisterUrlEvent;
use HeyFrame\Core\Checkout\Customer\Event\CustomerDoubleOptInRegistrationEvent;
use HeyFrame\Core\Checkout\Customer\Event\CustomerLoginEvent;
use HeyFrame\Core\Checkout\Customer\Event\CustomerRegisterEvent;
use HeyFrame\Core\Checkout\Customer\Event\DoubleOptInGuestOrderEvent;
use HeyFrame\Core\Checkout\Customer\Event\GuestCustomerRegisterEvent;
use HeyFrame\Core\Checkout\Customer\Service\EmailIdnConverter;
use HeyFrame\Core\Checkout\Customer\Validation\Constraint\CustomerEmailUnique;
use HeyFrame\Core\Checkout\Customer\Validation\Constraint\CustomerVatIdentification;
use HeyFrame\Core\Checkout\Customer\Validation\Constraint\CustomerZipCode;
use HeyFrame\Core\Checkout\Order\Channel\OrderService;
use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityRepository;
use HeyFrame\Core\Framework\DataAbstractionLayer\Indexing\EntityIndexerRegistry;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Criteria;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use HeyFrame\Core\Framework\DataAbstractionLayer\Validation\EntityExists;
use HeyFrame\Core\Framework\Event\DataMappingEvent;
use HeyFrame\Core\Framework\Feature;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Plugin\Exception\DecorationPatternException;
use HeyFrame\Core\Framework\Routing\StoreApiRouteScope;
use HeyFrame\Core\Framework\Util\Hasher;
use HeyFrame\Core\Framework\Uuid\Uuid;
use HeyFrame\Core\Framework\Validation\BuildValidationEvent;
use HeyFrame\Core\Framework\Validation\DataBag\DataBag;
use HeyFrame\Core\Framework\Validation\DataBag\RequestDataBag;
use HeyFrame\Core\Framework\Validation\DataValidationDefinition;
use HeyFrame\Core\Framework\Validation\DataValidationFactoryInterface;
use HeyFrame\Core\Framework\Validation\DataValidator;
use HeyFrame\Core\Framework\Validation\Exception\ConstraintViolationException;
use HeyFrame\Core\PlatformRequest;
use HeyFrame\Core\System\Channel\Aggregate\ChannelDomain\ChannelDomainCollection;
use HeyFrame\Core\System\Channel\Aggregate\ChannelDomain\ChannelDomainEntity;
use HeyFrame\Core\System\Channel\ChannelContext;
use HeyFrame\Core\System\Channel\Context\ChannelContextPersister;
use HeyFrame\Core\System\Channel\Context\ChannelContextServiceInterface;
use HeyFrame\Core\System\Channel\Context\ChannelContextServiceParameters;
use HeyFrame\Core\System\Channel\Entity\ChannelRepository;
use HeyFrame\Core\System\Channel\StoreApiCustomFieldMapper;
use HeyFrame\Core\System\Country\CountryCollection;
use HeyFrame\Core\System\NumberRange\ValueGenerator\NumberRangeValueGeneratorInterface;
use HeyFrame\Core\System\Salutation\SalutationCollection;
use HeyFrame\Core\System\Salutation\SalutationDefinition;
use HeyFrame\Core\System\SystemConfig\SystemConfigService;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Contracts\EventDispatcher\Event;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

#[Route(defaults: [PlatformRequest::ATTRIBUTE_ROUTE_SCOPE => [StoreApiRouteScope::ID]])]
#[Package('checkout')]
class RegisterRoute extends AbstractRegisterRoute
{
    /**
     * @internal
     *
     * @param EntityRepository<CustomerCollection> $customerRepository
     * @param ChannelRepository<CountryCollection> $countryRepository
     * @param EntityRepository<SalutationCollection> $salutationRepository
     */
    public function __construct(
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly NumberRangeValueGeneratorInterface $numberRangeValueGenerator,
        private readonly DataValidator $validator,
        private readonly DataValidationFactoryInterface $accountValidationFactory,
        private readonly DataValidationFactoryInterface $addressValidationFactory,
        private readonly SystemConfigService $systemConfigService,
        private readonly EntityRepository $customerRepository,
        private readonly ChannelContextPersister $contextPersister,
        private readonly ChannelRepository $countryRepository,
        protected Connection $connection,
        private readonly ChannelContextServiceInterface $contextService,
        private readonly StoreApiCustomFieldMapper $customFieldMapper,
        private readonly EntityRepository $salutationRepository,
        private readonly DataValidationFactoryInterface $passwordValidationFactory,
    ) {
    }

    public function getDecorated(): AbstractRegisterRoute
    {
        throw new DecorationPatternException(self::class);
    }

    #[Route(path: '/store-api/account/register', name: 'store-api.account.register', methods: ['POST'])]
    public function register(
        RequestDataBag $data,
        ChannelContext $context,
        bool $validateStorefrontUrl = true,
        ?DataValidationDefinition $additionalValidationDefinitions = null
    ): CustomerResponse {
        EmailIdnConverter::encodeDataBag($data);

        $isGuest = $data->getBoolean('guest');

        if ($data->has('accountType') && empty($data->get('accountType'))) {
            $data->remove('accountType');
        }

        if (!$data->get('salutationId')) {
            $data->set('salutationId', $this->getDefaultSalutationId($context));
        }

        $billing = $data->get('billingAddress');
        $shipping = $data->get('shippingAddress');

        if ($billing instanceof DataBag) {
            if ($billing->has('firstName') && !$data->has('firstName')) {
                $data->set('firstName', $billing->get('firstName'));
            }

            if ($billing->has('lastName') && !$data->has('lastName')) {
                $data->set('lastName', $billing->get('lastName'));
            }

            if ($data->has('title')) {
                $billing->set('title', $data->get('title'));
            }
        }

        $this->validateRegistrationData($data, $isGuest, $context, $additionalValidationDefinitions, $validateStorefrontUrl);

        $customer = $this->mapCustomerData($data, $isGuest, $context);

        if ($billing instanceof DataBag) {
            $billingAddress = $this->mapAddressData($billing, $context->getContext(), CustomerEvents::MAPPING_REGISTER_ADDRESS_BILLING);
            $billingAddress['id'] = Uuid::randomHex();
            $billingAddress['customerId'] = $customer['id'];
            $customer['defaultBillingAddressId'] = $billingAddress['id'];
            $customer['addresses'][] = $billingAddress;

            if (!$shipping) {
                $customer['defaultShippingAddressId'] = $billingAddress['id'];
            }
        }

        if ($shipping instanceof DataBag) {
            $shippingAddress = $this->mapAddressData($shipping, $context->getContext(), CustomerEvents::MAPPING_REGISTER_ADDRESS_SHIPPING);
            $shippingAddress['id'] = Uuid::randomHex();
            $shippingAddress['customerId'] = $customer['id'];

            $customer['defaultShippingAddressId'] = $shippingAddress['id'];
            $customer['addresses'][] = $shippingAddress;

            if (!$billing) {
                $customer['defaultBillingAddressId'] = $shippingAddress['id'];
            }
        }

        if ($data->get('accountType')) {
            $customer['accountType'] = $data->get('accountType');
        }

        $companyName = $billingAddress['company'] ?? $shippingAddress['company'] ?? null;
        if ($data->get('accountType') === CustomerEntity::ACCOUNT_TYPE_BUSINESS && $companyName) {
            $customer['company'] = $companyName;
            if ($data->get('vatIds')) {
                $customer['vatIds'] = $data->get('vatIds');
            }
        }

        $customer = $this->addDoubleOptInData($customer, $context);

        $customer['boundChannelId'] = $this->getBoundChannelId($customer['email'], $context);

        if ($data->get('customFields') instanceof RequestDataBag) {
            $customer['customFields'] = $this->customFieldMapper->map(CustomerDefinition::ENTITY_NAME, $data->get('customFields'));
        }

        // Convert all DataBags to array
        $customer = array_map(static function (mixed $value) {
            if ($value instanceof DataBag) {
                return $value->all();
            }

            return $value;
        }, $customer);

        $writeContext = clone $context->getContext();
        $writeContext->addState(EntityIndexerRegistry::USE_INDEXING_QUEUE);

        $this->customerRepository->create([$customer], $writeContext);

        $criteria = new Criteria([$customer['id']]);

        $customerEntity = $this->customerRepository->search($criteria, $context->getContext())->getEntities()->first();
        \assert(assertion: $customerEntity !== null);

        if ($customerEntity->getDoubleOptInRegistration()) {
            $this->eventDispatcher->dispatch(
                $this->getDoubleOptInEvent(
                    $customerEntity,
                    $context,
                    $data->get('storefrontUrl'),
                    $data->get('redirectTo'),
                    $data->get('redirectParameters')
                )
            );

            // We don't want to leak the hash in store-api
            $customerEntity->setHash('');

            return new CustomerResponse($customerEntity);
        }

        $response = new CustomerResponse($customerEntity);

        $newToken = $this->contextPersister->replace($context->getToken(), $context);

        $this->contextPersister->save(
            $newToken,
            [
                'customerId' => $customerEntity->getId(),
                'billingAddressId' => null,
                'shippingAddressId' => null,
                'domainId' => $context->getDomainId(),
            ],
            $context->getChannelId(),
            $customerEntity->getId()
        );

        $new = $this->contextService->get(
            new ChannelContextServiceParameters(
                $context->getChannelId(),
                $newToken,
                $context->getLanguageId(),
                $context->getCurrencyId(),
                $context->getDomainId(),
                null,
                $customerEntity->getId()
            )
        );

        $new->addState(...$context->getStates());

        if (!$customerEntity->getGuest()) {
            $this->eventDispatcher->dispatch(new CustomerRegisterEvent($new, $customerEntity));
        } else {
            $this->eventDispatcher->dispatch(new GuestCustomerRegisterEvent($new, $customerEntity));
        }

        $event = new CustomerLoginEvent($new, $customerEntity, $newToken);
        $this->eventDispatcher->dispatch($event);

        $response->headers->set(PlatformRequest::HEADER_CONTEXT_TOKEN, $newToken);

        // We don't want to leak the hash in store-api
        $customerEntity->setHash('');

        return $response;
    }

    private function getDoubleOptInEvent(
        CustomerEntity $customer,
        ChannelContext $context,
        string $url,
        ?string $redirectTo,
        ?string $redirectParameters
    ): Event {
        $url .= $this->getConfirmUrl($context, $customer);

        if ($redirectTo) {
            $params = \is_string($redirectParameters) ? (\json_decode($redirectParameters, true) ?? []) : [];
            $url .= '&' . \http_build_query(array_merge(['redirectTo' => $redirectTo], $params));
        }

        if ($customer->getGuest()) {
            $event = new DoubleOptInGuestOrderEvent($customer, $context, $url);
        } else {
            $event = new CustomerDoubleOptInRegistrationEvent($customer, $context, $url);
        }

        return $event;
    }

    /**
     * @param array<string, mixed> $customer
     *
     * @return array<string, mixed>
     */
    private function addDoubleOptInData(array $customer, ChannelContext $context): array
    {
        $configKey = $customer['guest']
            ? 'core.loginRegistration.doubleOptInGuestOrder'
            : 'core.loginRegistration.doubleOptInRegistration';

        $doubleOptInRequired = $this->systemConfigService
            ->get($configKey, $context->getChannelId());

        if (!$doubleOptInRequired) {
            return $customer;
        }

        $customer['doubleOptInRegistration'] = true;
        $customer['doubleOptInEmailSentDate'] = new \DateTimeImmutable();
        $customer['hash'] = Uuid::randomHex();

        return $customer;
    }

    private function validateRegistrationData(
        DataBag $data,
        bool $isGuest,
        ChannelContext $context,
        ?DataValidationDefinition $additionalValidations,
        bool $validateStorefrontUrl
    ): void {
        $billingAddress = $data->get('billingAddress');
        $shippingAddress = $data->get('shippingAddress');
        if ($billingAddress instanceof DataBag) {
            $billingAddress->set('firstName', $data->get('firstName'));
            $billingAddress->set('lastName', $data->get('lastName'));
            $billingAddress->set('salutationId', $data->get('salutationId'));
        }

        $definition = $this->getCustomerCreateValidationDefinition($isGuest, $data, $context);

        if ($additionalValidations) {
            $definition->merge($additionalValidations);
        }

        if ($validateStorefrontUrl) {
            $definition
                ->add('storefrontUrl', new NotBlank(), new Choice($this->getDomainUrls($context)));
        }

        $accountType = $data->get('accountType', CustomerEntity::ACCOUNT_TYPE_PRIVATE);
        if ($billingAddress instanceof DataBag || !($shippingAddress instanceof DataBag)) {
            $definition->addSub('billingAddress', $this->getCreateAddressValidationDefinition($data, $accountType, $billingAddress ?? new RequestDataBag(), $context));
        }

        if ($shippingAddress instanceof DataBag) {
            $shippingAccountType = $shippingAddress->get('accountType', CustomerEntity::ACCOUNT_TYPE_PRIVATE);
            $definition->addSub('shippingAddress', $this->getCreateAddressValidationDefinition($data, $shippingAccountType, $shippingAddress, $context));
        }

        if ($data->get('vatIds') instanceof DataBag) {
            $vatIds = array_filter($data->get('vatIds')->all());
            $data->set('vatIds', $vatIds);
        }

        if ($accountType === CustomerEntity::ACCOUNT_TYPE_BUSINESS) {
            $countryId = $shippingAddress instanceof DataBag
                ? $shippingAddress->get('countryId')
                : ($billingAddress instanceof DataBag ? $billingAddress->get('countryId') : null);

            if ($countryId) {
                if ($this->requiredVatIdField($countryId, $context)) {
                    $definition->add('vatIds', new NotBlank());
                }

                $definition->add('vatIds', new Type('array'), new CustomerVatIdentification(
                    countryId: $countryId
                ));
            }
        }

        if ($this->systemConfigService->get('core.loginRegistration.requireDataProtectionCheckbox', $context->getChannelId())) {
            $definition->add('acceptedDataProtection', new NotBlank());
        }

        $violations = $this->validator->getViolations($data->all(), $definition);

        if (!$violations->count()) {
            return;
        }

        throw new ConstraintViolationException($violations, $data->all());
    }

    /**
     * @return list<string>
     */
    private function getDomainUrls(ChannelContext $context): array
    {
        $channelDomainCollection = $context->getChannel()->getDomains();
        \assert($channelDomainCollection instanceof ChannelDomainCollection);

        return array_values(array_map(static fn (ChannelDomainEntity $domainEntity) => rtrim($domainEntity->getUrl(), '/'), $channelDomainCollection->getElements()));
    }

    private function getBirthday(DataBag $data): ?\DateTimeInterface
    {
        $birthdayDay = $data->get('birthdayDay');
        $birthdayMonth = $data->get('birthdayMonth');
        $birthdayYear = $data->get('birthdayYear');

        if (!\is_numeric($birthdayDay) || !\is_numeric($birthdayMonth) || !\is_numeric($birthdayYear)) {
            return null;
        }

        return new \DateTime(\sprintf(
            '%d-%d-%d',
            $birthdayYear,
            $birthdayMonth,
            $birthdayDay
        ));
    }

    /**
     * @return array<string, mixed>
     */
    private function mapCustomerData(DataBag $data, bool $isGuest, ChannelContext $context): array
    {
        $customer = [
            'customerNumber' => $this->numberRangeValueGenerator->getValue(
                $this->customerRepository->getDefinition()->getEntityName(),
                $context->getContext(),
                $context->getChannelId()
            ),
            'channelId' => $context->getChannelId(),
            'languageId' => $context->getLanguageId(),
            'groupId' => $context->getCustomerGroupId(),
            'requestedGroupId' => $data->get('requestedGroupId', null),
            'salutationId' => $data->get('salutationId'),
            'firstName' => $data->get('firstName'),
            'lastName' => $data->get('lastName'),
            'email' => $data->get('email'),
            'title' => $data->get('title'),
            'affiliateCode' => $data->get(OrderService::AFFILIATE_CODE_KEY),
            'campaignCode' => $data->get(OrderService::CAMPAIGN_CODE_KEY),
            'active' => true,
            'birthday' => $this->getBirthday($data),
            'guest' => $isGuest,
            'firstLogin' => new \DateTimeImmutable(),
            'addresses' => [],
        ];

        if (!$isGuest) {
            $customer['password'] = $data->get('password');
        }

        $event = new DataMappingEvent($data, $customer, $context->getContext());
        $this->eventDispatcher->dispatch($event, CustomerEvents::MAPPING_REGISTER_CUSTOMER);

        $customer = $event->getOutput();
        $customer['id'] = Uuid::randomHex();

        return $customer;
    }

    private function getCreateAddressValidationDefinition(
        DataBag $data,
        ?string $accountType,
        DataBag $address,
        ChannelContext $context
    ): DataValidationDefinition {
        $validation = $this->addressValidationFactory->create($context);

        if ($accountType === CustomerEntity::ACCOUNT_TYPE_BUSINESS
            && $this->systemConfigService->get('core.loginRegistration.showAccountTypeSelection', $context->getChannelId())) {
            $validation->add('company', new NotBlank());
        }

        $validation->set('zipcode', new CustomerZipCode(countryId: $address->get('countryId')));
        $validation->add('zipcode', new Length(max: 50));

        $validationEvent = new BuildValidationEvent($validation, $data, $context->getContext());
        $this->eventDispatcher->dispatch($validationEvent, $validationEvent->getName());

        return $validation;
    }

    private function getCustomerCreateValidationDefinition(bool $isGuest, DataBag $data, ChannelContext $context): DataValidationDefinition
    {
        $validation = $this->accountValidationFactory->create($context);

        $criteria = (new Criteria())
            ->addFilter(new EqualsFilter('registrationChannels.id', $context->getChannelId()));

        $validation->add('requestedGroupId', new EntityExists(
            entity: 'customer_group',
            context: $context->getContext(),
            criteria: $criteria,
        ));

        if (!$isGuest) {
            $validation->merge(
                $this->passwordValidationFactory->create($context)
            );
            $validation->add('email', new CustomerEmailUnique(channelContext: $context));
        }

        $validationEvent = new BuildValidationEvent($validation, $data, $context->getContext());
        $this->eventDispatcher->dispatch($validationEvent, $validationEvent->getName());

        return $validation;
    }

    /**
     * @return array<string, mixed>
     */
    private function mapAddressData(DataBag $addressData, Context $context, string $eventName): array
    {
        $mappedData = $addressData->only(
            'title',
            'firstName',
            'lastName',
            'salutationId',
            'street',
            'zipcode',
            'city',
            'company',
            'department',
            'countryStateId',
            'countryId',
            'additionalAddressLine1',
            'additionalAddressLine2',
            'phoneNumber'
        );

        if (isset($mappedData['countryStateId']) && $mappedData['countryStateId'] === '') {
            $mappedData['countryStateId'] = null;
        }

        if ($addressData->get('customFields') instanceof RequestDataBag) {
            $mappedData['customFields'] = $this->customFieldMapper->map(CustomerAddressDefinition::ENTITY_NAME, $addressData->get('customFields'));
        }

        $event = new DataMappingEvent($addressData, $mappedData, $context);
        $this->eventDispatcher->dispatch($event, $eventName);

        return $event->getOutput();
    }

    private function getBoundChannelId(string $email, ChannelContext $context): ?string
    {
        $bindCustomers = $this->systemConfigService->get('core.systemWideLoginRegistration.isCustomerBoundToChannel');
        $channelId = $context->getChannelId();

        if ($bindCustomers) {
            return $channelId;
        }

        if ($this->hasBoundAccount($email)) {
            return $channelId;
        }

        return null;
    }

    private function hasBoundAccount(string $email): bool
    {
        $query = $this->connection->createQueryBuilder();

        $results = $query
            ->select('LOWER(HEX(bound_channel_id)) as bound_channel_id')
            ->from('customer')
            ->where($query->expr()->eq('email', $query->createPositionalParameter($email)))
            ->executeQuery()
            ->fetchAllAssociative();

        foreach ($results as $result) {
            if ($result['bound_channel_id']) {
                return true;
            }
        }

        return false;
    }

    private function requiredVatIdField(string $countryId, ChannelContext $context): bool
    {
        if (!Feature::isActive('v6.8.0.0')) {
            $country = $this->countryRepository->search(new Criteria([$countryId]), $context)->get($countryId);

            if (!$country) {
                throw CustomerException::countryNotFound($countryId);
            }

            return $country->getVatIdRequired();
        }

        $countryCriteria = (new Criteria([$countryId]))
            ->addFields(['vatIdRequired']);

        $country = $this->countryRepository->search($countryCriteria, $context)->getEntities()->first();
        if (!$country) {
            throw CustomerException::countryNotFound($countryId);
        }

        return $country->get('vatIdRequired');
    }

    private function getConfirmUrl(ChannelContext $context, CustomerEntity $customer): string
    {
        $urlTemplate = $this->systemConfigService->get(
            'core.loginRegistration.confirmationUrl',
            $context->getChannelId()
        );
        if (!\is_string($urlTemplate)) {
            $urlTemplate = '/registration/confirm?em=%%HASHEDEMAIL%%&hash=%%SUBSCRIBEHASH%%';
        }

        $emailHash = Hasher::hash($customer->getEmail(), 'sha1');

        $urlEvent = new CustomerConfirmRegisterUrlEvent($context, $urlTemplate, $emailHash, $customer->getHash(), $customer);
        $this->eventDispatcher->dispatch($urlEvent);

        return str_replace(
            ['%%HASHEDEMAIL%%', '%%SUBSCRIBEHASH%%'],
            [$emailHash, (string) $customer->getHash()],
            $urlEvent->getConfirmUrl()
        );
    }

    private function getDefaultSalutationId(ChannelContext $context): ?string
    {
        $criteria = (new Criteria())
            ->setLimit(1)
            ->addFilter(new EqualsFilter('salutationKey', SalutationDefinition::NOT_SPECIFIED));

        return $this->salutationRepository->searchIds($criteria, $context->getContext())->firstId();
    }
}
