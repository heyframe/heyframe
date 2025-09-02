<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Customer\Channel;

use Doctrine\DBAL\Connection;
use HeyFrame\Core\Checkout\Customer\CustomerCollection;
use HeyFrame\Core\Checkout\Customer\CustomerDefinition;
use HeyFrame\Core\Checkout\Customer\CustomerEvents;
use HeyFrame\Core\Checkout\Customer\Event\CustomerLoginEvent;
use HeyFrame\Core\Checkout\Customer\Event\CustomerRegisterEvent;
use HeyFrame\Core\Checkout\Customer\Service\EmailIdnConverter;
use HeyFrame\Core\Checkout\Customer\Validation\Constraint\CustomerEmailUnique;
use HeyFrame\Core\Checkout\Order\Channel\OrderService;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityRepository;
use HeyFrame\Core\Framework\DataAbstractionLayer\Indexing\EntityIndexerRegistry;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Criteria;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use HeyFrame\Core\Framework\DataAbstractionLayer\Validation\EntityExists;
use HeyFrame\Core\Framework\Event\DataMappingEvent;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Plugin\Exception\DecorationPatternException;
use HeyFrame\Core\Framework\Routing\FrontApiRouteScope;
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
use HeyFrame\Core\System\Channel\FrontApiCustomFieldMapper;
use HeyFrame\Core\System\NumberRange\ValueGenerator\NumberRangeValueGeneratorInterface;
use HeyFrame\Core\System\SystemConfig\SystemConfigService;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

#[Route(defaults: [PlatformRequest::ATTRIBUTE_ROUTE_SCOPE => [FrontApiRouteScope::ID]])]
#[Package('checkout')]
class RegisterRoute extends AbstractRegisterRoute
{
    /**
     * @param EntityRepository<CustomerCollection> $customerRepository
     *
     * @internal
     */
    public function __construct(
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly NumberRangeValueGeneratorInterface $numberRangeValueGenerator,
        private readonly DataValidator $validator,
        private readonly DataValidationFactoryInterface $accountValidationFactory,
        private readonly SystemConfigService $systemConfigService,
        private readonly EntityRepository $customerRepository,
        private readonly ChannelContextPersister $contextPersister,
        protected Connection $connection,
        private readonly ChannelContextServiceInterface $contextService,
        private readonly FrontApiCustomFieldMapper $customFieldMapper,
        private readonly DataValidationFactoryInterface $passwordValidationFactory,
    ) {
    }

    public function getDecorated(): AbstractRegisterRoute
    {
        throw new DecorationPatternException(self::class);
    }

    #[Route(path: '/front-api/account/register', name: 'front-api.account.register', methods: ['POST'])]
    public function register(
        RequestDataBag $data,
        ChannelContext $context,
        bool $validateFrontendUrl = true,
        ?DataValidationDefinition $additionalValidationDefinitions = null
    ): CustomerResponse {
        EmailIdnConverter::encodeDataBag($data);

        $this->validateRegistrationData($data, $context, $additionalValidationDefinitions, $validateFrontendUrl);

        $customer = $this->mapCustomerData($data, $context);

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

        $response = new CustomerResponse($customerEntity);

        $newToken = $this->contextPersister->replace($context->getToken(), $context);

        $this->contextPersister->save(
            $newToken,
            [
                'customerId' => $customerEntity->getId(),
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

        $this->eventDispatcher->dispatch(new CustomerRegisterEvent($new, $customerEntity));

        $event = new CustomerLoginEvent($new, $customerEntity, $newToken);
        $this->eventDispatcher->dispatch($event);

        $response->headers->set(PlatformRequest::HEADER_CONTEXT_TOKEN, $newToken);

        // We don't want to leak the hash in front-api
        $customerEntity->setHash('');

        return $response;
    }

    private function validateRegistrationData(
        DataBag $data,
        ChannelContext $context,
        ?DataValidationDefinition $additionalValidations,
        bool $validateFrontendUrl
    ): void {
        $definition = $this->getCustomerCreateValidationDefinition($data, $context);

        if ($additionalValidations) {
            $definition->merge($additionalValidations);
        }

        if ($validateFrontendUrl) {
            $definition
                ->add('frontendUrl', new NotBlank(), new Choice($this->getDomainUrls($context)));
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
    private function mapCustomerData(DataBag $data, ChannelContext $context): array
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
            'firstLogin' => new \DateTimeImmutable(),
            'password' => $data->get('password'),
            'addresses' => [],
        ];

        $event = new DataMappingEvent($data, $customer, $context->getContext());
        $this->eventDispatcher->dispatch($event, CustomerEvents::MAPPING_REGISTER_CUSTOMER);

        $customer = $event->getOutput();
        $customer['id'] = Uuid::randomHex();

        return $customer;
    }

    private function getCustomerCreateValidationDefinition(DataBag $data, ChannelContext $context): DataValidationDefinition
    {
        $validation = $this->accountValidationFactory->create($context);

        $criteria = (new Criteria())
            ->addFilter(new EqualsFilter('registrationChannels.id', $context->getChannelId()));

        $validation->add('requestedGroupId', new EntityExists(
            entity: 'customer_group',
            context: $context->getContext(),
            criteria: $criteria,
        ));

        $validation->merge(
            $this->passwordValidationFactory->create($context)
        );
        $validation->add('email', new CustomerEmailUnique(channelContext: $context));

        $validationEvent = new BuildValidationEvent($validation, $data, $context->getContext());
        $this->eventDispatcher->dispatch($validationEvent, $validationEvent->getName());

        return $validation;
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
}
