<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Customer\Channel;

use HeyFrame\Core\Checkout\Customer\CustomerCollection;
use HeyFrame\Core\Checkout\Customer\CustomerEntity;
use HeyFrame\Core\Checkout\Customer\Validation\Constraint\CustomerPasswordMatches;
use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityRepository;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Plugin\Exception\DecorationPatternException;
use HeyFrame\Core\Framework\Routing\FrontApiRouteScope;
use HeyFrame\Core\Framework\Validation\BuildValidationEvent;
use HeyFrame\Core\Framework\Validation\DataBag\DataBag;
use HeyFrame\Core\Framework\Validation\DataBag\RequestDataBag;
use HeyFrame\Core\Framework\Validation\DataValidationDefinition;
use HeyFrame\Core\Framework\Validation\DataValidator;
use HeyFrame\Core\Framework\Validation\Exception\ConstraintViolationException;
use HeyFrame\Core\PlatformRequest;
use HeyFrame\Core\System\Channel\ChannelContext;
use HeyFrame\Core\System\Channel\ContextTokenResponse;
use HeyFrame\Core\System\SystemConfig\SystemConfigService;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Constraints\EqualTo;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

#[Route(defaults: [PlatformRequest::ATTRIBUTE_ROUTE_SCOPE => [FrontApiRouteScope::ID], '_contextTokenRequired' => true])]
#[Package('checkout')]
class ChangePasswordRoute extends AbstractChangePasswordRoute
{
    /**
     * @internal
     *
     * @param EntityRepository<CustomerCollection> $customerRepository
     */
    public function __construct(
        private readonly EntityRepository $customerRepository,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly SystemConfigService $systemConfigService,
        private readonly DataValidator $validator
    ) {
    }

    public function getDecorated(): AbstractChangePasswordRoute
    {
        throw new DecorationPatternException(self::class);
    }

    #[Route(path: '/front-api/account/change-password', name: 'front-api.account.change-password', defaults: ['_loginRequired' => true, '_acl_' => ['customer.password.change']], methods: ['POST'])]
    public function change(RequestDataBag $requestDataBag, ChannelContext $context, CustomerEntity $customer): ContextTokenResponse
    {
        $this->validatePasswordFields($requestDataBag, $context);

        $customerData = [
            'id' => $customer->getId(),
            'password' => $requestDataBag->get('newPassword'),
        ];

        $this->customerRepository->update([$customerData], $context->getContext());

        return new ContextTokenResponse($context->getToken());
    }

    private function dispatchValidationEvent(DataValidationDefinition $definition, DataBag $data, Context $context): void
    {
        $validationEvent = new BuildValidationEvent($definition, $data, $context);
        $this->eventDispatcher->dispatch($validationEvent, $validationEvent->getName());
    }

    /**
     * @throws ConstraintViolationException
     */
    private function validatePasswordFields(DataBag $data, ChannelContext $context): void
    {
        $definition = new DataValidationDefinition('customer.password.update');

        $minPasswordLength = $this->systemConfigService->getInt('core.loginRegistration.passwordMinLength', $context->getChannelId());
        if ($minPasswordLength < 0) {
            $minPasswordLength = null;
        }
        $definition
            ->add('newPassword', new NotBlank(), new Length(min: $minPasswordLength), new EqualTo(propertyPath: 'newPasswordConfirm'))
            ->add('password', new CustomerPasswordMatches(channelContext: $context));

        $this->dispatchValidationEvent($definition, $data, $context->getContext());

        $this->validator->validate($data->all(), $definition);

        $this->tryValidateEqualtoConstraint($data->all(), 'newPassword', $definition);
    }

    /**
     * @param mixed[] $data
     */
    private function tryValidateEqualtoConstraint(array $data, string $field, DataValidationDefinition $validation): void
    {
        $validations = $validation->getProperties();

        if (!\array_key_exists($field, $validations)) {
            return;
        }

        $fieldValidations = $validations[$field];

        $equalityValidation = null;

        foreach ($fieldValidations as $emailValidation) {
            if ($emailValidation instanceof EqualTo) {
                $equalityValidation = $emailValidation;

                break;
            }
        }

        if (!$equalityValidation instanceof EqualTo) {
            return;
        }

        $compareValue = $data[$equalityValidation->propertyPath] ?? null;
        if ($data[$field] === $compareValue) {
            return;
        }

        $message = str_replace('{{ compared_value }}', $compareValue, $equalityValidation->message);

        $violations = new ConstraintViolationList();
        $violations->add(new ConstraintViolation($message, $equalityValidation->message, [], '', $field, $data[$field]));

        throw new ConstraintViolationException($violations, $data);
    }
}
