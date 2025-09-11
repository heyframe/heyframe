<?php declare(strict_types=1);

namespace HeyFrame\Administration\Migration\V6_4;

use Doctrine\DBAL\Connection;
use HeyFrame\Core\Defaults;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Migration\MigrationStep;
use HeyFrame\Core\Framework\Uuid\Uuid;
use HeyFrame\Core\System\Channel\Aggregate\ChannelTranslation\ChannelTranslationDefinition;
use HeyFrame\Core\System\Language\LanguageDefinition;
use HeyFrame\Core\System\User\UserDefinition;

/**
 * @internal
 *
 * @codeCoverageIgnore
 */
#[Package('framework')]
class Migration1636121186CopyChannelIdsIntoUserConfig extends MigrationStep
{
    private const CONFIG_KEY = 'sales-channel-favorites';
    private const MAX_RESULTS = 7;

    public function getCreationTimestamp(): int
    {
        return 1636121186;
    }

    public function update(Connection $connection): void
    {
        $ids = $this->fetchUserChannelIds($connection);

        if (!$ids) {
            return;
        }

        $mapping = $this->getMappedData($ids);

        foreach ($mapping as $userId => $channelIds) {
            $slicedIds = \array_slice($channelIds, 0, self::MAX_RESULTS);

            $connection->insert('user_config', [
                'id' => Uuid::randomBytes(),
                'user_id' => $userId,
                '`key`' => self::CONFIG_KEY,
                '`value`' => json_encode($slicedIds, \JSON_THROW_ON_ERROR),
                'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT),
            ]);
        }
    }

    public function updateDestructive(Connection $connection): void
    {
        // implement update destructive
    }

    /**
     * @param list<array{userId: string, channelId: string, name: string}> $data
     *
     * @return array<string, list<string>>
     */
    private function getMappedData(array $data): array
    {
        $mapping = [];
        foreach ($data as $channelData) {
            $mapping[$channelData['userId']][] = $channelData['channelId'];
        }

        return $mapping;
    }

    /**
     * @return list<array{userId: string, channelId: string, name: string}>
     */
    private function fetchUserChannelIds(Connection $connection): array
    {
        /** @var list<array{userId: string, channelId: string, name: string}> $result */
        $result = $connection->createQueryBuilder()
            ->select('user.id AS userId')
            ->addSelect('LOWER(HEX(translation.channel_id)) AS channelId')
            ->addSelect('translation.name')
            ->from(UserDefinition::ENTITY_NAME, 'user')
            ->innerJoin('user', LanguageDefinition::ENTITY_NAME, 'language', 'user.locale_id = language.locale_id')
            ->innerJoin('language', ChannelTranslationDefinition::ENTITY_NAME, 'translation', 'translation.language_id = language.id')
            ->orderBy('translation.name', 'ASC')
            ->executeQuery()
            ->fetchAllAssociative();

        return $result;
    }
}
