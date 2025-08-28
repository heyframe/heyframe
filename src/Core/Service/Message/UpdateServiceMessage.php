<?php declare(strict_types=1);

namespace HeyFrame\Core\Service\Message;

use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\MessageQueue\AsyncMessageInterface;

/**
 * @internal
 */
#[Package('framework')]
readonly class UpdateServiceMessage implements AsyncMessageInterface
{
    public function __construct(public string $name)
    {
    }
}
