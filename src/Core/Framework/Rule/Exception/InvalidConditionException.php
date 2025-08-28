<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Rule\Exception;

use HeyFrame\Core\Framework\HeyFrameHttpException;
use HeyFrame\Core\Framework\Log\Package;

#[Package('fundamentals@after-sales')]
class InvalidConditionException extends HeyFrameHttpException
{
    public function __construct(string $conditionName)
    {
        parent::__construct('The condition "{{ condition }}" is invalid.', ['condition' => $conditionName]);
    }

    public function getErrorCode(): string
    {
        return 'FRAMEWORK__INVALID_CONDITION_ERROR';
    }
}
