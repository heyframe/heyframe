<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\SystemCheck\Check;

/**
 * @codeCoverageIgnore
 */
enum Status
{
    case OK;
    case UNKNOWN;

    case SKIPPED;

    case WARNING;

    case ERROR;

    case FAILURE;
}
