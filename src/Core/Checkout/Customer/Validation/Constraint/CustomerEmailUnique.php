<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Customer\Validation\Constraint;

use HeyFrame\Core\Checkout\Customer\CustomerException;
use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\Feature;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelContext;
use Symfony\Component\Validator\Attribute\HasNamedArguments;
use Symfony\Component\Validator\Constraint;

#[Package('checkout')]
class CustomerEmailUnique extends Constraint
{
    final public const CUSTOMER_EMAIL_NOT_UNIQUE = '79d30fe0-febf-421e-ac9b-1bfd5c9007f7';

    protected const ERROR_NAMES = [
        self::CUSTOMER_EMAIL_NOT_UNIQUE => 'CUSTOMER_EMAIL_NOT_UNIQUE',
    ];

    public string $message = 'The email address {{ email }} is already in use.';

    /**
     * @deprecated tag:v6.8.0 - Will be removed, use $channelContext instead
     */
    protected Context $context;

    protected ChannelContext $channelContext;

    /**
     * @param array{channelContext?: ChannelContext}|null $options
     *
     * @deprecated tag:v6.8.0 - reason:new-optional-parameter - $options parameter will be removed
     * @deprecated tag:v6.8.0 - reason:new-optional-parameter - $channelContext will be required and natively typed as constructor property promotion
     *
     * @internal
     */
    #[HasNamedArguments]
    public function __construct(?array $options = null, ?ChannelContext $channelContext = null)
    {
        if ($options !== null || $channelContext === null) {
            Feature::triggerDeprecationOrThrow(
                'v6.8.0.0',
                Feature::deprecatedMethodMessage(self::class, __METHOD__, 'v6.8.0.0', 'Use $channelContext argument instead of providing it in $options array')
            );
        }

        if ($options === null || Feature::isActive('v6.8.0.0')) {
            parent::__construct();

            if ($channelContext === null) {
                throw CustomerException::missingOption('channelContext', self::class);
            }

            $this->channelContext = $channelContext;
        } else {
            if (!($options['channelContext'] ?? null) instanceof ChannelContext) {
                throw CustomerException::missingOption('channelContext', self::class);
            }

            if (!isset($options['context'])) {
                $options['context'] = $options['channelContext']->getContext();
            }

            if (!($options['context'] ?? null) instanceof Context) {
                throw CustomerException::missingOption('context', self::class);
            }

            parent::__construct($options);
        }
    }

    /**
     * @deprecated tag:v6.8.0 - Will be removed, use getChannelContext instead
     */
    public function getContext(): Context
    {
        Feature::triggerDeprecationOrThrow(
            'v6.8.0.0',
            Feature::deprecatedMethodMessage(self::class, __METHOD__, 'v6.8.0.0', 'getChannelContext->getContext()')
        );

        return $this->channelContext->getContext();
    }

    public function getChannelContext(): ChannelContext
    {
        return $this->channelContext;
    }
}
