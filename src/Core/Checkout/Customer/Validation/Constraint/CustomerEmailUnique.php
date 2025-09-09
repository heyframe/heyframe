<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Customer\Validation\Constraint;

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

    protected string $message;

    protected ChannelContext $channelContext;

    /**
     * @internal
     */
    #[HasNamedArguments]
    public function __construct(ChannelContext $channelContext, string $message = 'The email address {{ email }} is already in use.')
    {
        $this->channelContext = $channelContext;
        $this->message = $message;
        parent::__construct();
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
