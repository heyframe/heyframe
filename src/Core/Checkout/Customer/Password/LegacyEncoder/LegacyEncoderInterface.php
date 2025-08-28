<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Customer\Password\LegacyEncoder;

use HeyFrame\Core\Framework\Log\Package;

#[Package('checkout')]
interface LegacyEncoderInterface
{
    public function getName(): string;

    public function isPasswordValid(#[\SensitiveParameter] string $password, string $hash): bool;
}
