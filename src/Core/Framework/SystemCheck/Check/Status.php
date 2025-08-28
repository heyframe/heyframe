<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\SystemCheck\Check;

use HeyFrame\Core\Framework\Log\Package;

/**
 * @codeCoverageIgnore
 */
#[Package('framework')]
enum Status
{
    case OK;
    case UNKNOWN;

    case SKIPPED;

    case WARNING;

    case ERROR;

    case FAILURE;
}
