<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Customer\Validation;

use HeyFrame\Core\Checkout\Customer\CustomerDefinition;
use HeyFrame\Core\Framework\DataAbstractionLayer\Validation\EntityExists;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Validation\DataValidationDefinition;
use HeyFrame\Core\Framework\Validation\DataValidationFactoryInterface;
use HeyFrame\Core\System\Channel\ChannelContext;
use HeyFrame\Core\System\SystemConfig\SystemConfigService;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\LessThanOrEqual;
use Symfony\Component\Validator\Constraints\NotBlank;

#[Package('checkout')]
class CustomerProfileValidationFactory implements DataValidationFactoryInterface
{
    /**
     * @param string[] $accountTypes
     *
     * @internal
     */
    public function __construct(
        private readonly SystemConfigService $systemConfigService,
    ) {
    }

    public function create(ChannelContext $context): DataValidationDefinition
    {
        $definition = new DataValidationDefinition('customer.profile.create');

        $this->addConstraints($definition, $context);

        return $definition;
    }

    public function update(ChannelContext $context): DataValidationDefinition
    {
        $definition = new DataValidationDefinition('customer.profile.update');

        $this->addConstraints($definition, $context);

        return $definition;
    }

    private function addConstraints(DataValidationDefinition $definition, ChannelContext $context): void
    {

        $channelId = $context->getChannelId();

        if ($this->systemConfigService->get('core.loginRegistration.showBirthdayField', $channelId)
            && $this->systemConfigService->get('core.loginRegistration.birthdayFieldRequired', $channelId)) {
            $definition
                ->add('birthdayDay', new GreaterThanOrEqual(value: 1), new LessThanOrEqual(value: 31))
                ->add('birthdayMonth', new GreaterThanOrEqual(value: 1), new LessThanOrEqual(value: 12))
                ->add('birthdayYear', new GreaterThanOrEqual(value: 1900), new LessThanOrEqual(value: date('Y')));
        }
    }
}
