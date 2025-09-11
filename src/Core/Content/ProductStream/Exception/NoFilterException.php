<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\ProductStream\Exception;

use HeyFrame\Core\Framework\HeyFrameHttpException;
use HeyFrame\Core\Framework\Log\Package;

#[Package('inventory')]
class NoFilterException extends HeyFrameHttpException
{
    public function __construct(string $id)
    {
        parent::__construct('Product stream with ID {{ id }} has no filters', ['id' => $id]);
    }

    public function getErrorCode(): string
    {
        return 'CONTENT__PRODUCT_STREAM_MISSING_FILTER';
    }
}
