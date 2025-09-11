<?php declare(strict_types=1);

namespace HeyFrame\Core\Migration\V6_3;

use Doctrine\DBAL\Connection;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Migration\MigrationStep;

/**
 * @internal
 *
 * @codeCoverageIgnore
 */
#[Package('framework')]
class Migration1536233530ChannelCategoryId extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1536233530;
    }

    public function update(Connection $connection): void
    {
        $this->addCmsToCategory($connection);
    }

    public function updateDestructive(Connection $connection): void
    {
    }

    private function addCmsToCategory(Connection $connection): void
    {
        $sql = <<<'SQL'
ALTER TABLE `category`
ADD COLUMN `cms_page_id` BINARY(16) NULL AFTER `media_id`,
ADD CONSTRAINT `fk.category.cms_page_id` FOREIGN KEY (`cms_page_id`)
REFERENCES `cms_page` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
SQL;

        $connection->executeStatement($sql);
    }
}
