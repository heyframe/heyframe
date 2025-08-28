<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Api\ApiDefinition\Generator\OpenApi;

use HeyFrame\Core\Framework\Log\Package;
use OpenApi\Analysis;

#[Package('framework')]
class DeactivateValidationAnalysis extends Analysis
{
    public function validate(): bool
    {
        return false;
        // deactivate Validitation
    }
}
