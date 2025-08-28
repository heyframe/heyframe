<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\SystemCheck;

use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\SystemCheck\Check\Category;
use HeyFrame\Core\Framework\SystemCheck\Check\Result;
use HeyFrame\Core\Framework\SystemCheck\Check\SystemCheckExecutionContext;

#[Package('framework')]
abstract class BaseCheck
{
    abstract public function run(): Result;

    abstract public function category(): Category;

    abstract public function name(): string;

    public function allowedToRunIn(SystemCheckExecutionContext $context): bool
    {
        return \in_array($context, $this->allowedSystemCheckExecutionContexts(), true);
    }

    /**
     * @return array<SystemCheckExecutionContext>
     */
    abstract protected function allowedSystemCheckExecutionContexts(): array;
}
