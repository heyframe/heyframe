<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Api\Context;

use HeyFrame\Core\Framework\Log\Package;

#[Package('framework')]
class SystemSource implements ContextSource
{
    public string $type = 'system';
}
