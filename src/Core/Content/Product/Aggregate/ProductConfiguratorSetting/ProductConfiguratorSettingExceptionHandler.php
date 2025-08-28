<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Product\Aggregate\ProductConfiguratorSetting;

use HeyFrame\Core\Content\Product\ProductException;
use HeyFrame\Core\Framework\DataAbstractionLayer\Dbal\ExceptionHandlerInterface;
use HeyFrame\Core\Framework\Log\Package;

#[Package('inventory')]
class ProductConfiguratorSettingExceptionHandler implements ExceptionHandlerInterface
{
    public function getPriority(): int
    {
        return ExceptionHandlerInterface::PRIORITY_DEFAULT;
    }

    public function matchException(\Throwable $e): ?\Throwable
    {
        if (preg_match('/SQLSTATE\[23000]:.*1062 Duplicate.*product_configurator_setting\.uniq\.product_configurator_setting\.prod_id\.vers_id\.prop_group_id\'/', $e->getMessage())) {
            return ProductException::configurationOptionAlreadyExists();
        }

        return null;
    }
}
