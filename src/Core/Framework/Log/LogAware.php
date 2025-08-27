<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Log;

use HeyFrame\Core\Framework\Event\IsFlowEventAware;
use Monolog\Level;

#[IsFlowEventAware]
interface LogAware
{
    /**
     * @return array<string, mixed>
     */
    public function getLogData(): array;

    public function getLogLevel(): Level;
}
