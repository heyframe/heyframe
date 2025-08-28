<?php
declare(strict_types=1);

namespace HeyFrame\Core\Framework\DataAbstractionLayer\Exception;

use HeyFrame\Core\Framework\DataAbstractionLayer\Field\Field;
use HeyFrame\Core\Framework\HeyFrameHttpException;
use HeyFrame\Core\Framework\Log\Package;

#[Package('framework')]
class MissingFieldSerializerException extends HeyFrameHttpException
{
    public function __construct(Field $field)
    {
        parent::__construct('No field serializer class found for field class "{{ class }}".', ['class' => $field::class]);
    }

    public function getErrorCode(): string
    {
        return 'FRAMEWORK__MISSING_FIELD_SERIALIZER';
    }
}
