<?php declare(strict_types=1);

namespace HeyFrame\Core\Migration\V6_3;

use Doctrine\DBAL\Connection;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Migration\InheritanceUpdaterTrait;
use HeyFrame\Core\Framework\Migration\MigrationStep;

/**
 * @internal
 *
 * @codeCoverageIgnore
 */
#[Package('framework')]
class Migration1604475913AddCMSPageIdToProductTable extends MigrationStep
{
    use InheritanceUpdaterTrait;

    public function getCreationTimestamp(): int
    {
        return 1604475913;
    }

    public function update(Connection $connection): void
    {
        $connection->executeStatement('
            ALTER TABLE `product`
            ADD COLUMN `cms_page_id` BINARY(16) NULL AFTER `product_media_version_id`,
            ADD CONSTRAINT `fk.product.cms_page_id` FOREIGN KEY (`cms_page_id`)
            REFERENCES `cms_page` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
        ');
        $this->updateInheritance($connection, 'product', 'cmsPage');
    }

    public function updateDestructive(Connection $connection): void
    {
    }
}
