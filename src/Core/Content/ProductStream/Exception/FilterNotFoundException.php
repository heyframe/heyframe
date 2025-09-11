<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\ProductStream\Exception;

use HeyFrame\Core\Framework\HeyFrameHttpException;
use HeyFrame\Core\Framework\Log\Package;

#[Package('inventory')]
class FilterNotFoundException extends HeyFrameHttpException
{
    public function __construct(string $type)
    {
        parent::__construct('Filter for type {{ type}} not found', ['type' => $type]);
    }

    public function getErrorCode(): string
    {
        return 'CONTENT__PRODUCT_STREAM_FILTER_NOT_FOUND';
    }
}
