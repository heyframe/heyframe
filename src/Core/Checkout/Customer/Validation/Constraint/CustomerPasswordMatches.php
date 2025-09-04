<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Customer\Validation\Constraint;

use HeyFrame\Core\Checkout\Customer\CustomerException;
use HeyFrame\Core\Framework\Feature;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelContext;
use Symfony\Component\Validator\Attribute\HasNamedArguments;
use Symfony\Component\Validator\Constraint;

#[Package('checkout')]
class CustomerPasswordMatches extends Constraint
{
    final public const CUSTOMER_PASSWORD_NOT_CORRECT = 'fe2faa88-34d9-4c3b-99b3-8158b1ed8dc7';

    protected const ERROR_NAMES = [
        self::CUSTOMER_PASSWORD_NOT_CORRECT => 'CUSTOMER_PASSWORD_NOT_CORRECT',
    ];

    /**
     * @deprecated tag:v6.8.0 - $message property access modifier will be changed to protected and is injectable via constructor
     */
    public string $message = 'Your password is wrong';

    /**
     * @deprecated tag:v6.8.0 - Will be removed, use $channelContext instead
     */
    protected ChannelContext $context;

    /**
     * @deprecated tag:v6.8.0 - Will be changed to natively typed in constructor injection
     */
    protected ChannelContext $channelContext;

    /**
     * @param ?array{channelContext: ChannelContext} $options
     *
     * @deprecated tag:v6.8.0 - reason:new-optional-parameter - $options parameter will be removed, use $channelContext instead
     * @deprecated tag:v6.8.0 - reason:new-optional-parameter - $channelContext parameter will be required and natively typed as constructor property promotion
     * @deprecated tag:v6.8.0 - reason:new-optional-parameter - $message will be natively typed as constructor property promotion
     *
     * @internal
     */
    #[HasNamedArguments]
    public function __construct(?array $options = null, ?ChannelContext $channelContext = null, string $message = 'Your password is wrong')
    {
        if ($options !== null || $channelContext === null) {
            Feature::triggerDeprecationOrThrow(
                'v6.8.0.0',
                Feature::deprecatedMethodMessage(self::class, __METHOD__, 'v6.8.0.0', 'Use $channelContext argument instead of providing it in $options array')
            );
        }

        if ($options === null || Feature::isActive('v6.8.0.0')) {
            if ($channelContext === null) {
                throw CustomerException::missingOption('channelContext', self::class);
            }

            parent::__construct();

            $this->channelContext = $channelContext;
            $this->message = $message;
        } else {
            if (isset($options['context'])) {
                $options['channelContext'] = $options['context'];
            }

            if (!($options['channelContext'] ?? null) instanceof ChannelContext) {
                throw CustomerException::missingOption('channelContext', self::class);
            }

            parent::__construct($options);
        }
    }

    /**
     * @deprecated tag:v6.8.0 - Will be removed, use getChannelContext instead
     */
    public function getContext(): ChannelContext
    {
        Feature::triggerDeprecationOrThrow(
            'v6.8.0.0',
            Feature::deprecatedMethodMessage(self::class, __METHOD__, 'v6.8.0.0', 'getChannelContext')
        );

        return $this->channelContext;
    }

    public function getChannelContext(): ChannelContext
    {
        return $this->channelContext;
    }

    public function getMessage(): string
    {
        return $this->message;
    }
}
