<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Customer\Channel;

use HeyFrame\Core\Checkout\Customer\Aggregate\CustomerAddress\CustomerAddressCollection;
use HeyFrame\Core\Checkout\Customer\Aggregate\CustomerAddress\CustomerAddressDefinition;
use HeyFrame\Core\Checkout\Customer\CustomerEntity;
use HeyFrame\Core\Checkout\Customer\CustomerEvents;
use HeyFrame\Core\Checkout\Customer\Validation\Constraint\CustomerZipCode;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityRepository;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Criteria;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use HeyFrame\Core\Framework\Event\DataMappingEvent;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Plugin\Exception\DecorationPatternException;
use HeyFrame\Core\Framework\Routing\StoreApiRouteScope;
use HeyFrame\Core\Framework\Uuid\Uuid;
use HeyFrame\Core\Framework\Validation\BuildValidationEvent;
use HeyFrame\Core\Framework\Validation\DataBag\DataBag;
use HeyFrame\Core\Framework\Validation\DataBag\RequestDataBag;
use HeyFrame\Core\Framework\Validation\DataValidationDefinition;
use HeyFrame\Core\Framework\Validation\DataValidationFactoryInterface;
use HeyFrame\Core\Framework\Validation\DataValidator;
use HeyFrame\Core\PlatformRequest;
use HeyFrame\Core\System\Channel\ChannelContext;
use HeyFrame\Core\System\Channel\StoreApiCustomFieldMapper;
use HeyFrame\Core\System\Salutation\SalutationCollection;
use HeyFrame\Core\System\Salutation\SalutationDefinition;
use HeyFrame\Core\System\SystemConfig\SystemConfigService;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

#[Route(defaults: [PlatformRequest::ATTRIBUTE_ROUTE_SCOPE => [StoreApiRouteScope::ID]])]
#[Package('checkout')]
class UpsertAddressRoute extends AbstractUpsertAddressRoute
{
    use CustomerAddressValidationTrait;

    /**
     * @internal
     *
     * @param EntityRepository<CustomerAddressCollection> $addressRepository
     * @param EntityRepository<SalutationCollection> $salutationRepository
     */
    public function __construct(
        private readonly EntityRepository $addressRepository,
        private readonly DataValidator $validator,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly DataValidationFactoryInterface $addressValidationFactory,
        private readonly SystemConfigService $systemConfigService,
        private readonly StoreApiCustomFieldMapper $storeApiCustomFieldMapper,
        private readonly EntityRepository $salutationRepository,
    ) {
    }

    public function getDecorated(): AbstractUpsertAddressRoute
    {
        throw new DecorationPatternException(self::class);
    }

    #[Route(
        path: '/store-api/account/address',
        name: 'store-api.account.address.create',
        defaults: ['addressId' => null, '_loginRequired' => true, '_loginRequiredAllowGuest' => true],
        methods: ['POST']
    )]
    #[Route(
        path: '/store-api/account/address/{addressId}',
        name: 'store-api.account.address.update',
        defaults: ['_loginRequired' => true, '_loginRequiredAllowGuest' => true],
        methods: ['PATCH']
    )]
    public function upsert(
        ?string $addressId,
        RequestDataBag $data,
        ChannelContext $context,
        CustomerEntity $customer
    ): UpsertAddressRouteResponse {
        if (!$addressId) {
            $isCreate = true;
            $addressId = Uuid::randomHex();
        } else {
            $this->validateAddress($addressId, $context, $customer);
            $isCreate = false;
        }

        if (!$data->get('salutationId')) {
            $data->set('salutationId', $this->getDefaultSalutationId($context));
        }

        $accountType = $data->get('accountType', CustomerEntity::ACCOUNT_TYPE_PRIVATE);
        $definition = $this->getValidationDefinition($data, $accountType, $isCreate, $context);
        $this->validator->validate(array_merge(['id' => $addressId], $data->all()), $definition);

        $addressData = [
            'salutationId' => $data->get('salutationId'),
            'firstName' => $data->get('firstName'),
            'lastName' => $data->get('lastName'),
            'street' => $data->get('street'),
            'city' => $data->get('city'),
            'zipcode' => $data->get('zipcode'),
            'countryId' => $data->get('countryId'),
            'countryStateId' => $data->get('countryStateId') ?: null,
            'company' => $data->get('company'),
            'department' => $data->get('department'),
            'title' => $data->get('title'),
            'phoneNumber' => $data->get('phoneNumber'),
            'additionalAddressLine1' => $data->get('additionalAddressLine1'),
            'additionalAddressLine2' => $data->get('additionalAddressLine2'),
        ];

        if ($data->get('customFields') instanceof RequestDataBag) {
            $addressData['customFields'] = $this->storeApiCustomFieldMapper->map(
                CustomerAddressDefinition::ENTITY_NAME,
                $data->get('customFields')
            );
            if ($addressData['customFields'] === []) {
                unset($addressData['customFields']);
            }
        }

        $mappingEvent = new DataMappingEvent($data, $addressData, $context->getContext());
        $this->eventDispatcher->dispatch($mappingEvent, CustomerEvents::MAPPING_ADDRESS_CREATE);

        $addressData = $mappingEvent->getOutput();
        $addressData['id'] = $addressId;
        $addressData['customerId'] = $customer->getId();

        $this->addressRepository->upsert([$addressData], $context->getContext());

        $address = $this->addressRepository->search(new Criteria([$addressId]), $context->getContext())->getEntities()->first();
        \assert($address !== null);

        return new UpsertAddressRouteResponse($address);
    }

    private function getValidationDefinition(
        DataBag $data,
        string $accountType,
        bool $isCreate,
        ChannelContext $context
    ): DataValidationDefinition {
        if ($isCreate) {
            $validation = $this->addressValidationFactory->create($context);
        } else {
            $validation = $this->addressValidationFactory->update($context);
        }

        if ($accountType === CustomerEntity::ACCOUNT_TYPE_BUSINESS
            && $this->systemConfigService->get('core.loginRegistration.showAccountTypeSelection')
        ) {
            $validation->add('company', new NotBlank());
        }

        $validation->set('zipcode', new CustomerZipCode(countryId: $data->get('countryId')));

        $validationEvent = new BuildValidationEvent($validation, $data, $context->getContext());
        $this->eventDispatcher->dispatch($validationEvent, $validationEvent->getName());

        return $validation;
    }

    private function getDefaultSalutationId(ChannelContext $context): ?string
    {
        $criteria = (new Criteria())
            ->setLimit(1)
            ->addFilter(new EqualsFilter('salutationKey', SalutationDefinition::NOT_SPECIFIED));

        return $this->salutationRepository->searchIds($criteria, $context->getContext())->firstId();
    }
}
