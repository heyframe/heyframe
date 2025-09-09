<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Customer\Validation\Constraint;

use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelContext;
use Symfony\Component\Validator\Attribute\HasNamedArguments;
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
     * @internal
     */
    #[HasNamedArguments]
    public function __construct(ChannelContext $channelContext, string $message = 'The nickname {{ nickname }} is already in use.')
    {
        parent::__construct();
        $this->channelContext = $channelContext;
        $this->message = $message;
    }

    public function getChannelContext(): ChannelContext
    {
        return $this->channelContext;
    }
}
