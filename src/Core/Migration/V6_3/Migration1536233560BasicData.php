<?php declare(strict_types=1);

namespace HeyFrame\Core\Migration\V6_3;

use Doctrine\DBAL\Connection;
use HeyFrame\Core\Checkout\Order\Aggregate\OrderTransaction\OrderTransactionStates;
use HeyFrame\Core\Checkout\Order\OrderStates;
use HeyFrame\Core\Checkout\Wallet\Cart\PaymentHandler\WalletPaymentHandler;
use HeyFrame\Core\Content\Navigation\NavigationDefinition;
use HeyFrame\Core\Defaults;
use HeyFrame\Core\Framework\Api\Util\AccessKeyHelper;
use HeyFrame\Core\Framework\DataAbstractionLayer\Doctrine\MultiInsertQueryQueue;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Migration\MigrationStep;
use HeyFrame\Core\Framework\Uuid\Uuid;

/**
 * @internal
 *
 * @codeCoverageIgnore
 */
#[Package('framework')]
class Migration1536233560BasicData extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1536233560;
    }

    public function update(Connection $connection): void
    {
        $hasData = $connection->executeQuery('SELECT 1 FROM `language` LIMIT 1')->fetchAssociative();
        if ($hasData) {
            return;
        }
        $this->createLanguage($connection);
        $this->createDict($connection);
        $this->createCountry($connection);
        $this->createCurrency($connection);
        $this->createCustomerGroup($connection);
        $this->createPaymentMethod($connection);
        $this->createRootNavigation($connection);
        $this->createChannelTypes($connection);
        $this->createChannel($connection);
        $this->createNumberRanges($connection);
        $this->createOrderStateMachine($connection);
        $this->createOrderTransactionStateMachine($connection);
        $this->createSystemConfigOptions($connection);
        $this->createDefaultSnippetSets($connection);
        $this->createDefaultMediaFolders($connection);
        $this->createCmsPages($connection);
    }

    private function createDefaultMediaFolders(Connection $connection): void
    {
        $queue = new MultiInsertQueryQueue($connection);

        $queue->addInsert('media_default_folder', ['id' => Uuid::randomBytes(), 'entity' => 'product', 'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);
        $queue->addInsert('media_default_folder', ['id' => Uuid::randomBytes(), 'entity' => 'user', 'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);
        $queue->addInsert('media_default_folder', ['id' => Uuid::randomBytes(), 'entity' => 'customer', 'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);
        $queue->addInsert('media_default_folder', ['id' => Uuid::randomBytes(), 'entity' => 'cms_page', 'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);
        $queue->addInsert('media_default_folder', ['id' => Uuid::randomBytes(), 'entity' => 'payment_method', 'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);
        $queue->addInsert('media_default_folder', ['id' => Uuid::randomBytes(), 'entity' => 'import_export_profile', 'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);
        $queue->execute();

        $notCreatedDefaultFolders = $connection->executeQuery('
            SELECT `media_default_folder`.`id` default_folder_id, `media_default_folder`.`entity` entity
            FROM `media_default_folder`
                LEFT JOIN `media_folder` ON `media_folder`.`default_folder_id` = `media_default_folder`.`id`
            WHERE `media_folder`.`id` IS NULL
        ')->fetchAllAssociative();

        foreach ($notCreatedDefaultFolders as $notCreatedDefaultFolder) {
            $this->createDefaultFolder(
                $connection,
                $notCreatedDefaultFolder['default_folder_id'],
                $notCreatedDefaultFolder['entity']
            );
        }
    }

    private function createDefaultFolder(Connection $connection, string $defaultFolderId, string $entity): void
    {
        $connection->transactional(function (Connection $connection) use ($defaultFolderId, $entity): void {
            $configurationId = Uuid::randomBytes();
            $folderId = Uuid::randomBytes();
            $folderName = $this->getMediaFolderName($entity);
            $connection->executeStatement('
                INSERT INTO `media_folder_configuration` (`id`, `thumbnail_quality`, `create_thumbnails`, `private`, created_at)
                VALUES (:id, 80, 1, :private, :createdAt)
            ', [
                'id' => $configurationId,
                'createdAt' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT),
                'private' => 0,
            ]);

            $connection->executeStatement('
                INSERT into `media_folder` (`id`, `name`, `default_folder_id`, `media_folder_configuration_id`, `use_parent_configuration`, `child_count`, `created_at`)
                VALUES (:folderId, :folderName, :defaultFolderId, :configurationId, 0, 0, :createdAt)
            ', [
                'folderId' => $folderId,
                'folderName' => $folderName,
                'defaultFolderId' => $defaultFolderId,
                'configurationId' => $configurationId,
                'createdAt' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT),
            ]);
        });
    }

    private function getMediaFolderName(string $entity): string
    {
        $capitalizedEntityParts = array_map(
            static fn ($part) => ucfirst($part),
            explode('_', $entity)
        );

        return implode(' ', $capitalizedEntityParts) . ' Media';
    }

    private function createDefaultSnippetSets(Connection $connection): void
    {
        $queue = new MultiInsertQueryQueue($connection);

        $queue->addInsert('snippet_set', ['id' => Uuid::randomBytes(), 'name' => 'BASE zh-CN', 'base_file' => 'messages.zh', 'iso' => 'zh-CN', 'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);
        $queue->addInsert('snippet_set', ['id' => Uuid::randomBytes(), 'name' => 'BASE en-GB', 'base_file' => 'messages.en', 'iso' => 'en-GB', 'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);

        $queue->execute();
    }

    private function createDict(Connection $connection): void
    {
        $languageZH = Uuid::fromHexToBytes(Defaults::LANGUAGE_SYSTEM);

        $id = Uuid::randomBytes();
        $connection->insert('dict', ['id' => $id, '`key`' => 'gender', 'active' => 1, 'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);
        $connection->insert('dict_translation', ['dict_id' => $id, 'language_id' => $languageZH, 'label' => '性别', 'position' => 1, 'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);

        $maleId = Uuid::randomBytes();
        $connection->insert('dict_item', ['id' => $maleId, 'dict_id' => $id, 'value' => '{"_value": 1}', 'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);
        $connection->insert('dict_item_translation', ['dict_item_id' => $maleId, 'language_id' => $languageZH, 'label' => '男', 'position' => 1, 'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);

        $female = Uuid::randomBytes();
        $connection->insert('dict_item', ['id' => $female, 'dict_id' => $id, 'value' => '{"_value": 2}', 'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);
        $connection->insert('dict_item_translation', ['dict_item_id' => $female, 'language_id' => $languageZH, 'label' => '女', 'position' => 1, 'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);
    }

    private function createChannel(Connection $connection): void
    {
        $currencies = $connection->executeQuery('SELECT id FROM currency')->fetchFirstColumn();
        $languages = $connection->executeQuery('SELECT id FROM language')->fetchFirstColumn();
        $paymentMethods = $connection->executeQuery('SELECT id FROM payment_method')->fetchFirstColumn();
        $defaultPaymentMethod = $connection->executeQuery('SELECT id FROM payment_method WHERE active = 1 ORDER BY `position`')->fetchOne();
        $countryStatement = $connection->executeQuery('SELECT id FROM country WHERE active = 1 ORDER BY `position`');
        $defaultCountry = $countryStatement->fetchOne();
        $rootNavigationId = $connection->executeQuery('SELECT id FROM navigation')->fetchOne();

        $id = Uuid::fromHexToBytes('98432def39fc4624b33213a56b8c944d');
        $languageZH = Uuid::fromHexToBytes(Defaults::LANGUAGE_SYSTEM);

        $connection->insert('channel', [
            'id' => $id,
            'type_id' => Uuid::fromHexToBytes(Defaults::CHANNEL_TYPE_API),
            'access_key' => AccessKeyHelper::generateAccessKey('channel'),
            'active' => 1,
            'language_id' => Uuid::fromHexToBytes(Defaults::LANGUAGE_SYSTEM),
            'currency_id' => Uuid::fromHexToBytes(Defaults::CURRENCY),
            'payment_method_id' => $defaultPaymentMethod,
            'country_id' => $defaultCountry,
            'navigation_id' => $rootNavigationId,
            'navigation_version_id' => Uuid::fromHexToBytes(Defaults::LIVE_VERSION),
            'customer_group_id' => Uuid::fromHexToBytes('cfbd5018d38d41d8adca10d94fc8bdd6'),
            'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT),
        ]);

        $connection->insert('channel_translation', ['channel_id' => $id, 'language_id' => $languageZH, 'name' => 'Headless', 'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);

        // country
        $connection->insert('channel_country', ['channel_id' => $id, 'country_id' => $defaultCountry]);

        // currency
        foreach ($currencies as $currency) {
            $connection->insert('channel_currency', ['channel_id' => $id, 'currency_id' => $currency]);
        }

        // language
        foreach ($languages as $language) {
            $connection->insert('channel_language', ['channel_id' => $id, 'language_id' => $language]);
        }

        // payment methods
        foreach ($paymentMethods as $paymentMethod) {
            $connection->insert('channel_payment_method', ['channel_id' => $id, 'payment_method_id' => $paymentMethod]);
        }
    }

    private function createChannelTypes(Connection $connection): void
    {
        $languageZH = Uuid::fromHexToBytes(Defaults::LANGUAGE_SYSTEM);

        $frontend = Uuid::fromHexToBytes(Defaults::CHANNEL_TYPE_FRONTEND);
        $frontendApi = Uuid::fromHexToBytes(Defaults::CHANNEL_TYPE_API);

        $connection->insert('channel_type', ['id' => $frontend, 'icon_name' => 'regular-globe', 'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);
        $connection->insert('channel_type_translation', ['channel_type_id' => $frontend, 'language_id' => $languageZH, 'name' => 'Frontend', 'manufacturer' => 'HeyFrame AG', 'description' => '带有 HTML 网页的渠道', 'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);

        $connection->insert('channel_type', ['id' => $frontendApi, 'icon_name' => 'regular-rocket', 'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);
        $connection->insert('channel_type_translation', ['channel_type_id' => $frontendApi, 'language_id' => $languageZH, 'name' => 'Headless', 'manufacturer' => 'HeyFrame AG', 'description' => '仅提供 API 的渠道', 'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);
    }

    private function createRootNavigation(Connection $connection): void
    {
        $id = Uuid::randomBytes();
        $languageZH = Uuid::fromHexToBytes(Defaults::LANGUAGE_SYSTEM);
        $versionId = Uuid::fromHexToBytes(Defaults::LIVE_VERSION);

        $connection->insert('navigation', ['id' => $id, 'version_id' => $versionId, 'type' => NavigationDefinition::TYPE_PAGE, 'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);
        $connection->insert('navigation_translation', ['navigation_id' => $id, 'navigation_version_id' => $versionId, 'language_id' => $languageZH, 'name' => 'HeyFrame ｜ 一套系统，快速、轻松搭建 CMS 与 Commerce 平台', 'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);
    }

    private function createPaymentMethod(Connection $connection): void
    {
        $languageZH = Uuid::fromHexToBytes(Defaults::LANGUAGE_SYSTEM);

        $wallet = Uuid::randomBytes();
        $connection->insert('payment_method', ['id' => $wallet, 'handler_identifier' => WalletPaymentHandler::class, 'technical_name' => 'wallet', 'position' => 1, 'active' => 1, 'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);
        $connection->insert('payment_method_translation', ['payment_method_id' => $wallet, 'language_id' => $languageZH, 'name' => '余额支付', 'description' => '使用账号钱包余额支付', 'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);
    }

    private function createSystemConfigOptions(Connection $connection): void
    {
        $connection->insert('system_config', [
            'id' => Uuid::randomBytes(),
            'configuration_key' => 'core.store.apiUri',
            'configuration_value' => '{"_value": "https://api.heyframe.net"}',
            'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT),
        ]);

        $connection->insert('system_config', [
            'id' => Uuid::randomBytes(),
            'configuration_key' => 'core.listing.showReview',
            'configuration_value' => '{"_value": true}',
            'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT),
        ]);

        $connection->insert('system_config', [
            'id' => Uuid::randomBytes(),
            'configuration_key' => 'core.register.minPasswordLength',
            'configuration_value' => '{"_value": 8}',
            'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT),
        ]);

        $connection->insert('system_config', [
            'id' => Uuid::randomBytes(),
            'configuration_key' => 'core.cart.maxQuantity',
            'configuration_value' => '{"_value": "100"}',
            'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT),
        ]);

        $connection->insert('system_config', [
            'id' => Uuid::randomBytes(),
            'configuration_key' => 'core.loginRegistration.passwordMinLength',
            'configuration_value' => '{"_value": "8"}',
            'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT),
        ]);

        $connection->insert('system_config', [
            'id' => Uuid::randomBytes(),
            'configuration_key' => 'core.loginRegistration.invalidateSessionOnLogOut',
            'configuration_value' => json_encode(['_value' => false]),
            'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT),
        ]);
    }

    private function createOrderTransactionStateMachine(Connection $connection): void
    {
        $stateMachineId = Uuid::randomBytes();

        $openId = Uuid::randomBytes();
        $paidId = Uuid::randomBytes();
        $paidPartiallyId = Uuid::randomBytes();
        $cancelledId = Uuid::randomBytes();
        $remindedId = Uuid::randomBytes();
        $refundedId = Uuid::randomBytes();
        $refundedPartiallyId = Uuid::randomBytes();

        $germanId = Uuid::fromHexToBytes(Defaults::LANGUAGE_SYSTEM);

        $translationZH = ['language_id' => $germanId, 'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT)];

        // state machine
        $connection->insert('state_machine', [
            'id' => $stateMachineId,
            'technical_name' => OrderTransactionStates::STATE_MACHINE,
            'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT),
        ]);

        $connection->insert('state_machine_translation', array_merge($translationZH, [
            'state_machine_id' => $stateMachineId,
            'name' => '支付状态',
            'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT),
        ]));

        // states
        $connection->insert('state_machine_state', ['id' => $openId, 'state_machine_id' => $stateMachineId, 'technical_name' => OrderTransactionStates::STATE_OPEN, 'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);
        $connection->insert('state_machine_state_translation', array_merge($translationZH, ['state_machine_state_id' => $openId, 'name' => '待支付']));

        $connection->insert('state_machine_state', ['id' => $paidId, 'state_machine_id' => $stateMachineId, 'technical_name' => OrderTransactionStates::STATE_PAID, 'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);
        $connection->insert('state_machine_state_translation', array_merge($translationZH, ['state_machine_state_id' => $paidId, 'name' => '已支付']));

        $connection->insert('state_machine_state', ['id' => $paidPartiallyId, 'state_machine_id' => $stateMachineId, 'technical_name' => OrderTransactionStates::STATE_PARTIALLY_PAID, 'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);
        $connection->insert('state_machine_state_translation', array_merge($translationZH, ['state_machine_state_id' => $paidPartiallyId, 'name' => '已支付 (部分)']));

        $connection->insert('state_machine_state', ['id' => $refundedId, 'state_machine_id' => $stateMachineId, 'technical_name' => OrderTransactionStates::STATE_REFUNDED, 'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);
        $connection->insert('state_machine_state_translation', array_merge($translationZH, ['state_machine_state_id' => $refundedId, 'name' => '已退款']));

        $connection->insert('state_machine_state', ['id' => $refundedPartiallyId, 'state_machine_id' => $stateMachineId, 'technical_name' => OrderTransactionStates::STATE_PARTIALLY_REFUNDED, 'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);
        $connection->insert('state_machine_state_translation', array_merge($translationZH, ['state_machine_state_id' => $refundedPartiallyId, 'name' => '已退款 (部分)']));

        $connection->insert('state_machine_state', ['id' => $cancelledId, 'state_machine_id' => $stateMachineId, 'technical_name' => OrderTransactionStates::STATE_CANCELLED, 'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);
        $connection->insert('state_machine_state_translation', array_merge($translationZH, ['state_machine_state_id' => $cancelledId, 'name' => '已取消']));

        $connection->insert('state_machine_state', ['id' => $remindedId, 'state_machine_id' => $stateMachineId, 'technical_name' => OrderTransactionStates::STATE_REMINDED, 'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);
        $connection->insert('state_machine_state_translation', array_merge($translationZH, ['state_machine_state_id' => $remindedId, 'name' => '已提醒']));

        // transitions
        // from "open" to *
        $connection->insert('state_machine_transition', ['id' => Uuid::randomBytes(), 'state_machine_id' => $stateMachineId, 'action_name' => 'pay', 'from_state_id' => $openId, 'to_state_id' => $paidId, 'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);
        $connection->insert('state_machine_transition', ['id' => Uuid::randomBytes(), 'state_machine_id' => $stateMachineId, 'action_name' => 'pay_partially', 'from_state_id' => $openId, 'to_state_id' => $paidPartiallyId, 'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);
        $connection->insert('state_machine_transition', ['id' => Uuid::randomBytes(), 'state_machine_id' => $stateMachineId, 'action_name' => 'cancel', 'from_state_id' => $openId, 'to_state_id' => $cancelledId, 'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);
        $connection->insert('state_machine_transition', ['id' => Uuid::randomBytes(), 'state_machine_id' => $stateMachineId, 'action_name' => 'remind', 'from_state_id' => $openId, 'to_state_id' => $remindedId, 'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);

        // from "reminded" to *
        $connection->insert('state_machine_transition', ['id' => Uuid::randomBytes(), 'state_machine_id' => $stateMachineId, 'action_name' => 'pay', 'from_state_id' => $remindedId, 'to_state_id' => $paidId, 'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);
        $connection->insert('state_machine_transition', ['id' => Uuid::randomBytes(), 'state_machine_id' => $stateMachineId, 'action_name' => 'pay_partially', 'from_state_id' => $remindedId, 'to_state_id' => $paidPartiallyId, 'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);
        $connection->insert('state_machine_transition', ['id' => Uuid::randomBytes(), 'state_machine_id' => $stateMachineId, 'action_name' => 'cancel', 'from_state_id' => $remindedId, 'to_state_id' => $cancelledId, 'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);

        // from "paid_partially" to *
        $connection->insert('state_machine_transition', ['id' => Uuid::randomBytes(), 'state_machine_id' => $stateMachineId, 'action_name' => 'remind', 'from_state_id' => $paidPartiallyId, 'to_state_id' => $remindedId, 'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);
        $connection->insert('state_machine_transition', ['id' => Uuid::randomBytes(), 'state_machine_id' => $stateMachineId, 'action_name' => 'pay', 'from_state_id' => $paidPartiallyId, 'to_state_id' => $paidId, 'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);
        $connection->insert('state_machine_transition', ['id' => Uuid::randomBytes(), 'state_machine_id' => $stateMachineId, 'action_name' => 'refund_partially', 'from_state_id' => $paidPartiallyId, 'to_state_id' => $refundedPartiallyId, 'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);
        $connection->insert('state_machine_transition', ['id' => Uuid::randomBytes(), 'state_machine_id' => $stateMachineId, 'action_name' => 'refund', 'from_state_id' => $paidPartiallyId, 'to_state_id' => $refundedId, 'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);
        $connection->insert('state_machine_transition', ['id' => Uuid::randomBytes(), 'state_machine_id' => $stateMachineId, 'action_name' => 'cancel', 'from_state_id' => $paidPartiallyId, 'to_state_id' => $cancelledId, 'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);

        // from "paid" to *
        $connection->insert('state_machine_transition', ['id' => Uuid::randomBytes(), 'state_machine_id' => $stateMachineId, 'action_name' => 'refund_partially', 'from_state_id' => $paidId, 'to_state_id' => $refundedPartiallyId, 'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);
        $connection->insert('state_machine_transition', ['id' => Uuid::randomBytes(), 'state_machine_id' => $stateMachineId, 'action_name' => 'refund', 'from_state_id' => $paidId, 'to_state_id' => $refundedId, 'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);
        $connection->insert('state_machine_transition', ['id' => Uuid::randomBytes(), 'state_machine_id' => $stateMachineId, 'action_name' => 'cancel', 'from_state_id' => $paidId, 'to_state_id' => $cancelledId, 'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);

        // from "refunded_partially" to *
        $connection->insert('state_machine_transition', ['id' => Uuid::randomBytes(), 'state_machine_id' => $stateMachineId, 'action_name' => 'refund', 'from_state_id' => $refundedPartiallyId, 'to_state_id' => $refundedId, 'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);
        $connection->insert('state_machine_transition', ['id' => Uuid::randomBytes(), 'state_machine_id' => $stateMachineId, 'action_name' => 'cancel', 'from_state_id' => $refundedPartiallyId, 'to_state_id' => $cancelledId, 'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);

        // from "cancelled" to *
        $connection->insert('state_machine_transition', ['id' => Uuid::randomBytes(), 'state_machine_id' => $stateMachineId, 'action_name' => 'reopen', 'from_state_id' => $cancelledId, 'to_state_id' => $openId, 'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);
        $connection->insert('state_machine_transition', ['id' => Uuid::randomBytes(), 'state_machine_id' => $stateMachineId, 'action_name' => 'refund', 'from_state_id' => $cancelledId, 'to_state_id' => $refundedId, 'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);
        $connection->insert('state_machine_transition', ['id' => Uuid::randomBytes(), 'state_machine_id' => $stateMachineId, 'action_name' => 'refund_partially', 'from_state_id' => $cancelledId, 'to_state_id' => $refundedPartiallyId, 'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);

        // set initial state
        $connection->update('state_machine', ['initial_state_id' => $openId], ['id' => $stateMachineId]);
    }

    private function createOrderStateMachine(Connection $connection): void
    {
        $stateMachineId = Uuid::randomBytes();
        $openId = Uuid::randomBytes();
        $completedId = Uuid::randomBytes();
        $inProgressId = Uuid::randomBytes();
        $canceledId = Uuid::randomBytes();

        $chineseId = Uuid::fromHexToBytes(Defaults::LANGUAGE_SYSTEM);

        $translationZH = ['language_id' => $chineseId, 'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT)];

        // state machine
        $connection->insert('state_machine', [
            'id' => $stateMachineId,
            'technical_name' => OrderStates::STATE_MACHINE,
            'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT),
        ]);

        $connection->insert('state_machine_translation', array_merge($translationZH, [
            'state_machine_id' => $stateMachineId,
            'name' => '订单状态',
            'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT),
        ]));

        // states
        $connection->insert('state_machine_state', ['id' => $openId, 'state_machine_id' => $stateMachineId, 'technical_name' => OrderStates::STATE_OPEN, 'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);
        $connection->insert('state_machine_state_translation', array_merge($translationZH, ['state_machine_state_id' => $openId, 'name' => '待处理']));

        $connection->insert('state_machine_state', ['id' => $completedId, 'state_machine_id' => $stateMachineId, 'technical_name' => OrderStates::STATE_COMPLETED, 'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);
        $connection->insert('state_machine_state_translation', array_merge($translationZH, ['state_machine_state_id' => $completedId, 'name' => '已完成']));

        $connection->insert('state_machine_state', ['id' => $inProgressId, 'state_machine_id' => $stateMachineId, 'technical_name' => OrderStates::STATE_IN_PROGRESS, 'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);
        $connection->insert('state_machine_state_translation', array_merge($translationZH, ['state_machine_state_id' => $inProgressId, 'name' => '处理中']));

        $connection->insert('state_machine_state', ['id' => $canceledId, 'state_machine_id' => $stateMachineId, 'technical_name' => OrderStates::STATE_CANCELLED, 'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);
        $connection->insert('state_machine_state_translation', array_merge($translationZH, ['state_machine_state_id' => $canceledId, 'name' => '已取消']));

        // transitions
        $connection->insert('state_machine_transition', ['id' => Uuid::randomBytes(), 'state_machine_id' => $stateMachineId, 'action_name' => 'process', 'from_state_id' => $openId, 'to_state_id' => $inProgressId, 'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);
        $connection->insert('state_machine_transition', ['id' => Uuid::randomBytes(), 'state_machine_id' => $stateMachineId, 'action_name' => 'cancel', 'from_state_id' => $openId, 'to_state_id' => $canceledId, 'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);

        $connection->insert('state_machine_transition', ['id' => Uuid::randomBytes(), 'state_machine_id' => $stateMachineId, 'action_name' => 'cancel', 'from_state_id' => $inProgressId, 'to_state_id' => $canceledId, 'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);
        $connection->insert('state_machine_transition', ['id' => Uuid::randomBytes(), 'state_machine_id' => $stateMachineId, 'action_name' => 'complete', 'from_state_id' => $inProgressId, 'to_state_id' => $completedId, 'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);

        $connection->insert('state_machine_transition', ['id' => Uuid::randomBytes(), 'state_machine_id' => $stateMachineId, 'action_name' => 'reopen', 'from_state_id' => $canceledId, 'to_state_id' => $openId, 'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);
        $connection->insert('state_machine_transition', ['id' => Uuid::randomBytes(), 'state_machine_id' => $stateMachineId, 'action_name' => 'reopen', 'from_state_id' => $completedId, 'to_state_id' => $openId, 'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);
        // set initial state
        $connection->update('state_machine', ['initial_state_id' => $openId], ['id' => $stateMachineId]);
    }

    private function createNumberRanges(Connection $connection): void
    {
        $definitionNumberRangeTypes = [
            'product' => [
                'id' => Uuid::randomHex(),
                'global' => 1,
                'nameZh' => '产品',
                'nameEn' => 'Product',
            ],
            'order' => [
                'id' => Uuid::randomHex(),
                'global' => 0,
                'nameZh' => '订单',
                'nameEn' => 'Order',
            ],
            'customer' => [
                'id' => Uuid::randomHex(),
                'global' => 0,
                'nameZh' => '客户',
                'nameEn' => 'Customer',
            ],
        ];

        $definitionNumberRanges = [
            'product' => [
                'id' => Uuid::randomHex(),
                'name' => 'Products',
                'nameZh' => '产品',
                'global' => 1,
                'typeId' => $definitionNumberRangeTypes['product']['id'],
                'pattern' => '{date_ymd}{date_Hs}{n}',
                'start' => 1000000,
            ],
            'order' => [
                'id' => Uuid::randomHex(),
                'name' => 'Orders',
                'nameZh' => '订单',
                'global' => 1,
                'typeId' => $definitionNumberRangeTypes['order']['id'],
                'pattern' => '{date_ymd}{date_Hs}{n}',
                'start' => 1000000,
            ],
            'customer' => [
                'id' => Uuid::randomHex(),
                'name' => 'Customers',
                'nameZh' => '客户',
                'global' => 1,
                'typeId' => $definitionNumberRangeTypes['customer']['id'],
                'pattern' => '{date_ymd}{date_Hs}{n}',
                'start' => 1000000,
            ],
        ];

        $languageZh = Uuid::fromHexToBytes(Defaults::LANGUAGE_SYSTEM);

        foreach ($definitionNumberRangeTypes as $typeName => $numberRangeType) {
            $connection->insert(
                'number_range_type',
                [
                    'id' => Uuid::fromHexToBytes($numberRangeType['id']),
                    'global' => $numberRangeType['global'],
                    'technical_name' => $typeName,
                    'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT),
                ]
            );
            $connection->insert(
                'number_range_type_translation',
                [
                    'number_range_type_id' => Uuid::fromHexToBytes($numberRangeType['id']),
                    'type_name' => $numberRangeType['nameZh'],
                    'language_id' => $languageZh,
                    'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT),
                ]
            );
        }

        foreach ($definitionNumberRanges as $numberRange) {
            $connection->insert(
                'number_range',
                [
                    'id' => Uuid::fromHexToBytes($numberRange['id']),
                    'global' => $numberRange['global'],
                    'type_id' => Uuid::fromHexToBytes($numberRange['typeId']),
                    'pattern' => $numberRange['pattern'],
                    'start' => $numberRange['start'],
                    'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT),
                ]
            );

            $connection->insert(
                'number_range_translation',
                [
                    'number_range_id' => Uuid::fromHexToBytes($numberRange['id']),
                    'name' => $numberRange['nameZh'],
                    'language_id' => $languageZh,
                    'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT),
                ]
            );
        }
    }

    private function createCustomerGroup(Connection $connection): void
    {
        $connection->insert('customer_group', ['id' => Uuid::fromHexToBytes('cfbd5018d38d41d8adca10d94fc8bdd6'), 'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);
        $connection->insert('customer_group_translation', ['customer_group_id' => Uuid::fromHexToBytes('cfbd5018d38d41d8adca10d94fc8bdd6'), 'language_id' => Uuid::fromHexToBytes(Defaults::LANGUAGE_SYSTEM), 'name' => '普通客户组', 'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);
    }

    private function createCurrency(Connection $connection): void
    {
        $CNY = Uuid::fromHexToBytes(Defaults::CURRENCY);
        $USD = Uuid::randomBytes();
        $GBP = Uuid::randomBytes();
        $EUR = Uuid::randomBytes();

        $languageZH = Uuid::fromHexToBytes(Defaults::LANGUAGE_SYSTEM);

        $connection->insert('currency', ['id' => $CNY, 'iso_code' => 'CNY', 'factor' => 1, 'symbol' => '¥', 'position' => 1, 'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);
        $connection->insert('currency_translation', ['currency_id' => $CNY, 'language_id' => $languageZH, 'short_name' => 'CNY', 'name' => '人民币', 'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);

        $connection->insert('currency', ['id' => $EUR, 'iso_code' => 'EUR', 'factor' => 0.1205, 'symbol' => '€', 'position' => 1, 'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);
        $connection->insert('currency_translation', ['currency_id' => $EUR, 'language_id' => $languageZH, 'short_name' => 'EUR', 'name' => '欧元', 'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);

        $connection->insert('currency', ['id' => $USD, 'iso_code' => 'USD', 'factor' => 0.1396, 'symbol' => '$', 'position' => 1, 'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);
        $connection->insert('currency_translation', ['currency_id' => $USD, 'language_id' => $languageZH, 'short_name' => 'USD', 'name' => '美元', 'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);

        $connection->insert('currency', ['id' => $GBP, 'iso_code' => 'GBP', 'factor' => 0.1039, 'symbol' => '£', 'position' => 1, 'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);
        $connection->insert('currency_translation', ['currency_id' => $GBP, 'language_id' => $languageZH, 'short_name' => 'GBP', 'name' => '英镑', 'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);

        $rounding = json_encode([
            'decimals' => 2,
            'interval' => 0.01,
            'roundForNet' => true,
        ]);

        $connection->executeStatement('UPDATE `currency` SET item_rounding = :rounding,total_rounding=:rounding WHERE item_rounding IS NULL', ['rounding' => $rounding]);
    }

    private function createCountry(Connection $connection): void
    {
        $languageZH = static fn (string $countryId, string $name) => [
            'language_id' => Uuid::fromHexToBytes(Defaults::LANGUAGE_SYSTEM),
            'name' => $name,
            'country_id' => $countryId,
            'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT),
        ];

        $zhId = Uuid::randomBytes();
        $connection->insert('country', ['id' => $zhId, 'iso' => 'CN', 'position' => 1, 'iso3' => 'CHN', 'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);
        $connection->insert('country_translation', $languageZH($zhId, '中国'));
        $this->createCountryStates($connection, $zhId, 'CN');
    }

    private function createCountryStates(Connection $connection, string $countryId, string $countryCode): void
    {
        $data = [
            'CN' => [
                'BJ' => '北京市',
                'TJ' => '天津市',
                'HE' => '河北省',
                'SX' => '山西省',
                'NM' => '内蒙古自治区',
                'LN' => '辽宁省',
                'JL' => '吉林省',
                'HL' => '黑龙江省',
                'SH' => '上海市',
                'JS' => '江苏省',
                'ZJ' => '浙江省',
                'AH' => '安徽省',
                'FJ' => '福建省',
                'JX' => '江西省',
                'SD' => '山东省',
                'HA' => '河南省',
                'HB' => '湖北省',
                'HN' => '湖南省',
                'GD' => '广东省',
                'GX' => '广西壮族自治区',
                'HI' => '海南省',
                'CQ' => '重庆市',
                'SC' => '四川省',
                'GZ' => '贵州省',
                'YN' => '云南省',
                'XZ' => '西藏自治区',
                'SN' => '陕西省',
                'GS' => '甘肃省',
                'QH' => '青海省',
                'NX' => '宁夏回族自治区',
                'XJ' => '新疆维吾尔自治区',
                'TW' => '台湾省',
                'HK' => '香港特别行政区',
                'MO' => '澳门特别行政区',
            ],
        ];

        foreach ($data[$countryCode] as $isoCode => $name) {
            $storageDate = (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT);
            $id = Uuid::randomBytes();
            $countryStateData = [
                'id' => $id,
                'country_id' => $countryId,
                'short_code' => $isoCode,
                'created_at' => $storageDate,
            ];
            $connection->insert('country_state', $countryStateData);
            $connection->insert('country_state_translation', [
                'language_id' => Uuid::fromHexToBytes(Defaults::LANGUAGE_SYSTEM),
                'country_state_id' => $id,
                'name' => $name,
                'created_at' => $storageDate,
            ]);
        }
    }

    private function createLanguage(Connection $connection): void
    {
        $localeZh = Uuid::randomBytes();
        $languageZh = Uuid::fromHexToBytes(Defaults::LANGUAGE_SYSTEM);

        // first locales
        $connection->insert('locale', ['id' => $localeZh, 'code' => 'zh-CN', 'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT)]);

        $connection->insert('language', [
            'id' => $languageZh,
            'name' => '中文',
            'active' => 1,
            'locale_id' => $localeZh,
            'translation_code_id' => $localeZh,
            'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT),
        ]);

        $connection->insert('locale_translation', [
            'locale_id' => $localeZh,
            'language_id' => $languageZh,
            'name' => '中文',
            'territory' => '中国',
            'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT),
        ]);
    }

    private function createCmsPages(Connection $connection): void
    {
        $languageZh = Uuid::fromHexToBytes(Defaults::LANGUAGE_SYSTEM);

        // cms page
        $page = ['id' => Uuid::randomBytes(), 'type' => 'product_list', 'locked' => 1, 'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT)];
        $pageChinese = ['cms_page_id' => $page['id'], 'language_id' => $languageZh, 'name' => '默认列表布局', 'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT)];
        $connection->insert('cms_page', $page);
        $connection->insert('cms_page_translation', $pageChinese);
    }
}
