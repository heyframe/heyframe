<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Customer\Validation\Constraint;

use HeyFrame\Core\Checkout\Customer\CustomerException;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelContext;
use Symfony\Component\Validator\Constraint;

#[Package('checkout')]
class CustomerNicknameUnique extends Constraint
{
    final public const CUSTOMER_USERNAME_NOT_UNIQUE = 'e8b27d5f-3d43-47df-b0cc-a5f4fdf305bc';

    protected const ERROR_NAMES = [
        self::CUSTOMER_USERNAME_NOT_UNIQUE => 'CUSTOMER_USERNAME_NOT_UNIQUE',
    ];

    public string $message = 'The nickname {{ nickname }} is already in use.';

    protected ChannelContext $channelContext;

    /**
     * @param array{channelContext: ChannelContext} $options
     *
     * @internal
     */
    public function __construct(array $options)
    {
        if (!($options['channelContext'] ?? null) instanceof ChannelContext) {
            throw CustomerException::missingOption('channelContext', self::class);
        }

        parent::__construct($options);
    }

    public function getChannelContext(): ChannelContext
    {
        return $this->channelContext;
    }
}
