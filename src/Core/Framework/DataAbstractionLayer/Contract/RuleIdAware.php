<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\DataAbstractionLayer\Contract;

use HeyFrame\Core\Framework\Log\Package;

#[Package('framework')]
interface RuleIdAware
{
    public function getAvailabilityRuleId(): ?string;
}
