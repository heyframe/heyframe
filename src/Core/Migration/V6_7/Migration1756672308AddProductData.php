<?php declare(strict_types=1);

namespace HeyFrame\Core\Migration\V6_7;

use Doctrine\DBAL\Connection;
use HeyFrame\Core\Content\Product\ProductEntity;
use HeyFrame\Core\Defaults;
use HeyFrame\Core\Framework\DataAbstractionLayer\Pricing\Price;
use HeyFrame\Core\Framework\DataAbstractionLayer\Pricing\PriceCollection;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Migration\MigrationStep;
use HeyFrame\Core\Framework\Util\Json;
use HeyFrame\Core\Framework\Uuid\Uuid;

/**
 * @internal
 */
#[Package('framework')]
class Migration1756672308AddProductData extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1756672308;
    }

    public function update(Connection $connection): void
    {
        $defaultLangId = $this->getLanguageIdByLocale($connection, 'zh-CN');
        $enLangId = $this->getLanguageIdByLocale($connection, 'en-GB');

        $id = Uuid::randomBytes();
        $versionId = Uuid::fromHexToBytes(Defaults::LIVE_VERSION);
        $connection->insert('product', [
            'id' => $id,
            'version_id' => $versionId,
            'price' => Json::encode(new PriceCollection([
                new Price(Defaults::CURRENCY, 0),
            ])),
            'product_number' => 'S10000000001',
            'stock' => 0,
            'max_purchase' => 1,
            'product_type' => ProductEntity::PRODUCT_TYPE_WALLET_RECHARGE,
            'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT),
        ]);

        $connection->insert('product_translation', [
            'product_id' => $id,
            'product_version_id' => $versionId,
            'language_id' => $defaultLangId,
            'name' => '钱包充值',
            'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT),
        ]);

        $connection->insert('product_translation', [
            'product_id' => $id,
            'product_version_id' => $versionId,
            'language_id' => $enLangId,
            'name' => 'Wallet Recharge',
            'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT),
        ]);

        $groupId = Uuid::randomBytes();
        $connection->insert('property_group', [
            'id' => $groupId,
            'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT),
        ]);
        $connection->insert('property_group_translation', [
            'property_group_id' => $groupId,
            'language_id' => $defaultLangId,
            'name' => '客户计划',
            'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT),
        ]);
        $connection->insert('property_group_translation', [
            'property_group_id' => $groupId,
            'language_id' => $enLangId,
            'name' => 'Membership Plan',
            'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT),
        ]);
        $monthlyId = Uuid::randomBytes();
        $connection->insert('property_group_option', [
            'id' => $monthlyId,
            'property_group_id' => $groupId,
            'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT),
        ]);
        $connection->insert('property_group_option_translation', [
            'property_group_option_id' => $monthlyId,
            'language_id' => $defaultLangId,
            'name' => '月度客户',
            'position' => 1,
            'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT),
        ]);
        $connection->insert('property_group_option_translation', [
            'property_group_option_id' => $monthlyId,
            'language_id' => $enLangId,
            'name' => 'Monthly Membership',
            'position' => 1,
            'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT),
        ]);

        $yearlyId = Uuid::randomBytes();
        $connection->insert('property_group_option', [
            'id' => $yearlyId,
            'property_group_id' => $groupId,
            'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT),
        ]);
        $connection->insert('property_group_option_translation', [
            'property_group_option_id' => $yearlyId,
            'language_id' => $defaultLangId,
            'name' => '年度客户',
            'position' => 1,
            'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT),
        ]);
        $connection->insert('property_group_option_translation', [
            'property_group_option_id' => $yearlyId,
            'language_id' => $enLangId,
            'name' => 'Yearly Membership',
            'position' => 1,
            'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT),
        ]);

        $lifetimeId = Uuid::randomBytes();
        $connection->insert('property_group_option', [
            'id' => $lifetimeId,
            'property_group_id' => $groupId,
            'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT),
        ]);
        $connection->insert('property_group_option_translation', [
            'property_group_option_id' => $lifetimeId,
            'language_id' => $defaultLangId,
            'name' => '永久客户',
            'position' => 1,
            'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT),
        ]);
        $connection->insert('property_group_option_translation', [
            'property_group_option_id' => $lifetimeId,
            'language_id' => $enLangId,
            'name' => 'Lifetime Membership',
            'position' => 1,
            'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT),
        ]);
    }

    private function getLanguageIdByLocale(Connection $connection, string $locale): ?string
    {
        $sql = <<<'SQL'
SELECT `language`.`id`
FROM `language`
INNER JOIN `locale` ON `locale`.`id` = `language`.`locale_id`
WHERE `locale`.`code` = :code
SQL;

        $languageId = $connection->executeQuery($sql, ['code' => $locale])->fetchOne();
        if (!$languageId && $locale !== 'zh-CN') {
            return null;
        }

        if (!$languageId) {
            return Uuid::fromHexToBytes(Defaults::LANGUAGE_SYSTEM);
        }

        return $languageId;
    }
}
