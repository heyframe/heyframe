<?php declare(strict_types=1);

namespace HeyFrame\Core\Test\Integration\Traits;

use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Test\TestCaseBase\ChannelApiTestBehaviour;
use HeyFrame\Core\Framework\Test\TestCaseBase\IntegrationTestBehaviour;
use HeyFrame\Core\Framework\Util\Random;
use HeyFrame\Core\System\Channel\Context\ChannelContextPersister;
use HeyFrame\Core\Test\TestDefaults;

/**
 * @internal
 */
#[Package('checkout')]
trait CustomerTestTrait
{
    use ChannelApiTestBehaviour;
    use IntegrationTestBehaviour;

    private function getLoggedInContextToken(string $customerId, string $channelId = TestDefaults::CHANNEL): string
    {
        $token = Random::getAlphanumericString(32);
        static::getContainer()->get(ChannelContextPersister::class)->save(
            $token,
            [
                'customerId' => $customerId,
                'billingAddressId' => null,
                'shippingAddressId' => null,
            ],
            $channelId,
            $customerId
        );

        return $token;
    }
}
