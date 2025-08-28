<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Product\Channel\Sorting;

use HeyFrame\Core\Content\Product\Exception\DuplicateProductSortingKeyException;
use HeyFrame\Core\Framework\DataAbstractionLayer\Dbal\ExceptionHandlerInterface;
use HeyFrame\Core\Framework\Log\Package;

#[Package('inventory')]
class ProductSortingExceptionHandler implements ExceptionHandlerInterface
{
    public function getPriority(): int
    {
        return ExceptionHandlerInterface::PRIORITY_DEFAULT;
    }

    public function matchException(\Throwable $e): ?\Throwable
    {
        if (preg_match('/SQLSTATE\[23000\]:.*1062 Duplicate.*uniq.product_sorting.url_key\'/', $e->getMessage())) {
            $key = [];
            preg_match('/Duplicate entry \'(.*)\' for key/', $e->getMessage(), $key);
            $key = $key[1] ?? '';

            return new DuplicateProductSortingKeyException($key, $e);
        }

        return null;
    }
}
