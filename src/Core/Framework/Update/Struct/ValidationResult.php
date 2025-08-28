<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Update\Struct;

use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Struct\Struct;

#[Package('framework')]
class ValidationResult extends Struct
{
    /**
     * @param array<mixed> $vars
     */
    public function __construct(
        protected string $name,
        protected bool $result,
        protected string $message,
        protected array $vars = []
    ) {
    }

    public function getApiAlias(): string
    {
        return 'update_api_validation_result';
    }
}
