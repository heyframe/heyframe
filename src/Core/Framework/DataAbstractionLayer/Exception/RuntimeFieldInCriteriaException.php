<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\DataAbstractionLayer\Exception;

use HeyFrame\Core\Framework\HeyFrameHttpException;

class RuntimeFieldInCriteriaException extends HeyFrameHttpException
{
    public function __construct(string $field)
    {
        parent::__construct(
            'Field {{ field }} is a Runtime field and cannot be used in a criteria',
            ['field' => $field]
        );
    }

    public function getErrorCode(): string
    {
        return 'FRAMEWORK__RUNTIME_FIELD_IN_CRITERIA';
    }
}
