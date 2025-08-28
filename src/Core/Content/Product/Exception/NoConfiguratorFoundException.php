<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Product\Exception;

use HeyFrame\Core\Framework\HeyFrameHttpException;
use HeyFrame\Core\Framework\Log\Package;

#[Package('inventory')]
class NoConfiguratorFoundException extends HeyFrameHttpException
{
    public function __construct(string $productId)
    {
        parent::__construct(
            'Product with id {{ productId }} has no configuration.',
            ['productId' => $productId]
        );
    }

    public function getErrorCode(): string
    {
        return 'CONTENT__PRODUCT_HAS_NO_CONFIGURATOR';
    }
}
