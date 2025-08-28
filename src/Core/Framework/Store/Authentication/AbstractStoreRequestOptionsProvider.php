<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Store\Authentication;

use HeyFrame\Core\Framework\Api\Context\AdminApiSource;
use HeyFrame\Core\Framework\Api\Context\Exception\InvalidContextSourceException;
use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\Log\Package;

/**
 * @internal
 */
#[Package('checkout')]
abstract class AbstractStoreRequestOptionsProvider
{
    /**
     * @return array<string, string>
     */
    abstract public function getAuthenticationHeader(Context $context): array;

    /**
     * @return array<string, string>
     */
    abstract public function getDefaultQueryParameters(Context $context): array;

    protected function ensureAdminApiSource(Context $context): AdminApiSource
    {
        $contextSource = $context->getSource();
        if (!($contextSource instanceof AdminApiSource)) {
            throw new InvalidContextSourceException(AdminApiSource::class, $contextSource::class);
        }

        return $contextSource;
    }
}
