<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Product\Aggregate\ProductSearchConfig;

use HeyFrame\Core\Content\Product\Exception\DuplicateProductSearchConfigLanguageException;
use HeyFrame\Core\Framework\DataAbstractionLayer\Dbal\ExceptionHandlerInterface;
use HeyFrame\Core\Framework\Log\Package;

#[Package('inventory')]
class ProductSearchConfigExceptionHandler implements ExceptionHandlerInterface
{
    public function getPriority(): int
    {
        return ExceptionHandlerInterface::PRIORITY_DEFAULT;
    }

    public function matchException(\Throwable $e): ?\Throwable
    {
        if (preg_match('/SQLSTATE\[23000\]:.*1062 Duplicate.*uniq.product_search_config.language_id\'/', $e->getMessage())) {
            return new DuplicateProductSearchConfigLanguageException('', $e);
        }

        return null;
    }
}
