<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Customer\Channel;

use HeyFrame\Core\Checkout\Customer\Aggregate\CustomerRecovery\CustomerRecoveryCollection;
use HeyFrame\Core\Checkout\Customer\Aggregate\CustomerRecovery\CustomerRecoveryEntity;
use HeyFrame\Core\Checkout\Customer\CustomerException;
use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityRepository;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Criteria;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Plugin\Exception\DecorationPatternException;
use HeyFrame\Core\Framework\Routing\StoreApiRouteScope;
use HeyFrame\Core\Framework\Validation\BuildValidationEvent;
use HeyFrame\Core\Framework\Validation\DataBag\DataBag;
use HeyFrame\Core\Framework\Validation\DataBag\RequestDataBag;
use HeyFrame\Core\Framework\Validation\DataValidationDefinition;
use HeyFrame\Core\Framework\Validation\DataValidator;
use HeyFrame\Core\Framework\Validation\Exception\ConstraintViolationException;
use HeyFrame\Core\PlatformRequest;
use HeyFrame\Core\System\Channel\ChannelContext;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

#[Route(defaults: [PlatformRequest::ATTRIBUTE_ROUTE_SCOPE => [StoreApiRouteScope::ID]])]
#[Package('checkout')]
class CustomerRecoveryIsExpiredRoute extends AbstractCustomerRecoveryIsExpiredRoute
{
    /**
     * @internal
     *
     * @param EntityRepository<CustomerRecoveryCollection> $customerRecoveryRepository
     */
    public function __construct(
        private readonly EntityRepository $customerRecoveryRepository,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly DataValidator $validator
    ) {
    }

    public function getDecorated(): AbstractCustomerRecoveryIsExpiredRoute
    {
        throw new DecorationPatternException(self::class);
    }

    #[Route(path: '/store-api/account/customer-recovery-is-expired', name: 'store-api.account.customer.recovery.is.expired', methods: ['POST'])]
    public function load(RequestDataBag $data, ChannelContext $context): CustomerRecoveryIsExpiredResponse
    {
        $this->validateHash($data, $context);

        $hash = $data->get('hash');

        $customerHashCriteria = new Criteria();
        $customerHashCriteria->addFilter(new EqualsFilter('hash', $hash));

        $customerRecovery = $this->customerRecoveryRepository->search($customerHashCriteria, $context->getContext())->getEntities()->first();
        if (!$customerRecovery) {
            throw CustomerException::customerNotFoundByHash($hash);
        }

        return new CustomerRecoveryIsExpiredResponse($this->isExpired($customerRecovery));
    }

    /**
     * @throws ConstraintViolationException
     */
    private function validateHash(DataBag $data, ChannelContext $context): void
    {
        $definition = new DataValidationDefinition('customer.recovery.get');

        $hashLength = 32;

        $definition->add('hash', new NotBlank(), new Type('string'), new Length($hashLength));

        $this->dispatchValidationEvent($definition, $data, $context->getContext());

        $this->validator->validate($data->all(), $definition);
    }

    private function dispatchValidationEvent(DataValidationDefinition $definition, DataBag $data, Context $context): void
    {
        $validationEvent = new BuildValidationEvent($definition, $data, $context);
        $this->eventDispatcher->dispatch($validationEvent, $validationEvent->getName());
    }

    private function isExpired(CustomerRecoveryEntity $customerRecovery): bool
    {
        $validDateTime = (new \DateTime())->sub(new \DateInterval('PT2H'));

        return $validDateTime > $customerRecovery->getCreatedAt();
    }
}
