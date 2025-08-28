<?php declare(strict_types=1);

namespace HeyFrame\Core\Service\Message;

use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\MessageQueue\AsyncMessageInterface;

/**
 * @internal
 *
 * @codeCoverageIgnore
 */
#[Package('framework')]
readonly class InstallServicesMessage implements AsyncMessageInterface
{
}
