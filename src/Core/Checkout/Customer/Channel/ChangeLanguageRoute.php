<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Customer\Channel;

use HeyFrame\Core\Checkout\Customer\CustomerCollection;
use HeyFrame\Core\Checkout\Customer\CustomerEntity;
use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityRepository;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Criteria;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use HeyFrame\Core\Framework\DataAbstractionLayer\Validation\EntityExists;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Plugin\Exception\DecorationPatternException;
use HeyFrame\Core\Framework\Routing\StoreApiRouteScope;
use HeyFrame\Core\Framework\Validation\BuildValidationEvent;
use HeyFrame\Core\Framework\Validation\Constraint\Uuid;
use HeyFrame\Core\Framework\Validation\DataBag\DataBag;
use HeyFrame\Core\Framework\Validation\DataBag\RequestDataBag;
use HeyFrame\Core\Framework\Validation\DataValidationDefinition;
use HeyFrame\Core\Framework\Validation\DataValidator;
use HeyFrame\Core\PlatformRequest;
use HeyFrame\Core\System\Channel\ChannelContext;
use HeyFrame\Core\System\Channel\SuccessResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

#[Route(defaults: [PlatformRequest::ATTRIBUTE_ROUTE_SCOPE => [StoreApiRouteScope::ID], '_contextTokenRequired' => true])]
#[Package('checkout')]
class ChangeLanguageRoute extends AbstractChangeLanguageRoute
{
    /**
     * @internal
     *
     * @param EntityRepository<CustomerCollection> $customerRepository
     */
    public function __construct(
        private readonly EntityRepository $customerRepository,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly DataValidator $validator
    ) {
    }

    public function getDecorated(): AbstractChangeLanguageRoute
    {
        throw new DecorationPatternException(self::class);
    }

    #[Route(path: '/store-api/account/change-language', name: 'store-api.account.change-language', methods: ['POST'], defaults: ['_loginRequired' => true])]
    public function change(RequestDataBag $requestDataBag, ChannelContext $context, CustomerEntity $customer): SuccessResponse
    {
        $this->validateLanguageId($requestDataBag, $context);

        $customerData = [
            'id' => $customer->getId(),
            'languageId' => $requestDataBag->get('languageId'),
        ];

        $this->customerRepository->update([$customerData], $context->getContext());

        return new SuccessResponse();
    }

    private function validateLanguageId(DataBag $data, ChannelContext $context): void
    {
        $validation = new DataValidationDefinition('customer.language.update');

        $languageCriteria = new Criteria([$data->get('languageId')]);
        $languageCriteria->addFilter(new EqualsFilter('channels.id', $context->getChannelId()));

        $validation->add('languageId', new Uuid())
            ->add('languageId', new EntityExists(entity: 'language', context: $context->getContext(), criteria: $languageCriteria));

        $this->dispatchValidationEvent($validation, $data, $context->getContext());

        $this->validator->validate($data->all(), $validation);
    }

    private function dispatchValidationEvent(DataValidationDefinition $definition, DataBag $data, Context $context): void
    {
        $validationEvent = new BuildValidationEvent($definition, $data, $context);
        $this->eventDispatcher->dispatch($validationEvent, $validationEvent->getName());
    }
}
