<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Store\Services;

use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Plugin\Exception\DecorationPatternException;
use HeyFrame\Core\Framework\Store\Struct\ReviewStruct;

/**
 * @internal
 */
#[Package('checkout')]
class ExtensionStoreLicensesService extends AbstractExtensionStoreLicensesService
{
    public function __construct(private readonly StoreClient $client)
    {
    }

    public function cancelSubscription(int $licenseId, Context $context): void
    {
        $this->client->cancelSubscription($licenseId, $context);
    }

    public function rateLicensedExtension(ReviewStruct $rating, Context $context): void
    {
        $this->client->createRating($rating, $context);
    }

    /**
     * @codeCoverageIgnore
     */
    protected function getDecorated(): AbstractExtensionStoreLicensesService
    {
        throw new DecorationPatternException(self::class);
    }
}
