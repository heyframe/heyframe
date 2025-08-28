<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Gateway\Context\Command;

use HeyFrame\Core\Framework\Log\Package;

#[Package('framework')]
class LoginCustomerCommand extends AbstractContextGatewayCommand implements TokenCommandInterface
{
    public const COMMAND_KEY = 'context_login-customer';

    public function __construct(
        public readonly string $customerEmail,
    ) {
    }

    public static function getDefaultKeyName(): string
    {
        return self::COMMAND_KEY;
    }
}
