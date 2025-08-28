<?php declare(strict_types=1);

namespace HeyFrame\Core\Service\Permission;

use HeyFrame\Core\Framework\Log\Package;

/**
 * @internal
 *
 * @codeCoverageIgnore
 */
#[Package('framework')]
enum ConsentState: string
{
    case GRANTED = 'granted';
    case REVOKED = 'revoked';
}
