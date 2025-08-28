<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\MessageQueue\ScheduledTask\MessageQueue;

use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\MessageQueue\AsyncMessageInterface;

#[Package('framework')]
class RegisterScheduledTaskMessage implements AsyncMessageInterface
{
}
