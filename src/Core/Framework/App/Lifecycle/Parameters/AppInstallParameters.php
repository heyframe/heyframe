<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\App\Lifecycle\Parameters;

use HeyFrame\Core\Framework\Log\Package;

/**
 * @internal
 *
 * @codeCoverageIgnore This is a simple DTO and does not require tests
 */
#[Package('framework')]
final readonly class AppInstallParameters
{
    public function __construct(
        public bool $activate = true,
        public bool $acceptPermissions = true
    ) {
    }
}
