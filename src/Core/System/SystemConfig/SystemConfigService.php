<?php declare(strict_types=1);

namespace HeyFrame\Core\System\SystemConfig;

use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;
use HeyFrame\Core\Defaults;
use HeyFrame\Core\Framework\Adapter\Cache\CacheTagCollector;
use HeyFrame\Core\Framework\Bundle;
use HeyFrame\Core\Framework\DataAbstractionLayer\Doctrine\MultiInsertQueryQueue;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\ConfigJsonField;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Util\Json;
use HeyFrame\Core\Framework\Uuid\Exception\InvalidUuidException;
use HeyFrame\Core\Framework\Uuid\Uuid;
use HeyFrame\Core\System\SystemConfig\Event\BeforeSystemConfigChangedEvent;
use HeyFrame\Core\System\SystemConfig\Event\BeforeSystemConfigMultipleChangedEvent;
use HeyFrame\Core\System\SystemConfig\Event\SystemConfigChangedEvent;
use HeyFrame\Core\System\SystemConfig\Event\SystemConfigDomainLoadedEvent;
use HeyFrame\Core\System\SystemConfig\Event\SystemConfigMultipleChangedEvent;
use HeyFrame\Core\System\SystemConfig\Exception\BundleConfigNotFoundException;
use HeyFrame\Core\System\SystemConfig\Util\ConfigReader;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Service\ResetInterface;

#[Package('framework')]
class SystemConfigService implements ResetInterface
{
    /**
     * @var array<string, string>|null
     */
    private ?array $appMapping = null;

    /**
     * @internal
     */
    public function __construct(
        private readonly Connection $connection,
        private readonly ConfigReader $configReader,
        private readonly AbstractSystemConfigLoader $loader,
        private readonly EventDispatcherInterface $dispatcher,
        private readonly SymfonySystemConfigService $symfonySystemConfigService,
        private readonly CacheTagCollector $cacheTagCollector,
    ) {
    }

    public static function buildName(string $key): string
    {
        return 'config.' . $key;
    }

    /**
     * @return array<mixed>|bool|float|int|string|null
     */
    public function get(string $key, ?string $channelId = null)
    {
        $this->cacheTagCollector->addTag('system.config-' . $channelId);

        $config = $this->loader->load($channelId);

        $parts = explode('.', $key);

        $pointer = $config;

        foreach ($parts as $part) {
            if (!\is_array($pointer)) {
                return null;
            }

            if (\array_key_exists($part, $pointer)) {
                $pointer = $pointer[$part];

                continue;
            }

            return null;
        }

        return $pointer;
    }

    public function getString(string $key, ?string $channelId = null): string
    {
        $value = $this->get($key, $channelId);
        if (!\is_array($value)) {
            return (string) $value;
        }

        throw SystemConfigException::invalidSettingValueException($key, 'string', \gettype($value));
    }

    public function getInt(string $key, ?string $channelId = null): int
    {
        $value = $this->get($key, $channelId);
        if (!\is_array($value)) {
            return (int) $value;
        }

        throw SystemConfigException::invalidSettingValueException($key, 'int', \gettype($value));
    }

    public function getFloat(string $key, ?string $channelId = null): float
    {
        $value = $this->get($key, $channelId);
        if (!\is_array($value)) {
            return (float) $value;
        }

        throw SystemConfigException::invalidSettingValueException($key, 'float', \gettype($value));
    }

    public function getBool(string $key, ?string $channelId = null): bool
    {
        return (bool) $this->get($key, $channelId);
    }

    /**
     * @internal should not be used in frontend or store api. The cache layer caches all accessed config keys and use them as cache tag.
     *
     * gets all available shop configs and returns them as an array
     *
     * @return array<mixed>
     */
    public function all(?string $channelId = null): array
    {
        return $this->loader->load($channelId);
    }

    /**
     * @internal should not be used in frontend or store api. The cache layer caches all accessed config keys and use them as cache tag.
     *
     * @throws SystemConfigException
     *
     * @return array<mixed>
     */
    public function getDomain(string $domain, ?string $channelId = null, bool $inherit = false): array
    {
        $domain = trim($domain);
        if ($domain === '') {
            throw SystemConfigException::invalidDomain('Empty domain');
        }

        $queryBuilder = $this->connection->createQueryBuilder()
            ->select('configuration_key', 'configuration_value')
            ->from('system_config');

        if ($inherit) {
            $queryBuilder->where('channel_id IS NULL OR channel_id = :channelId');
        } elseif ($channelId === null) {
            $queryBuilder->where('channel_id IS NULL');
        } else {
            $queryBuilder->where('channel_id = :channelId');
        }

        $domain = rtrim($domain, '.') . '.';
        $escapedDomain = str_replace('%', '\\%', $domain);

        $channelId = $channelId ? Uuid::fromHexToBytes($channelId) : null;

        $queryBuilder->andWhere('configuration_key LIKE :prefix')
            ->addOrderBy('channel_id', 'ASC')
            ->setParameter('prefix', $escapedDomain . '%')
            ->setParameter('channelId', $channelId);

        $configs = $queryBuilder->executeQuery()->fetchAllNumeric();

        if ($configs === []) {
            return [];
        }

        $merged = [];

        foreach ($configs as [$key, $value]) {
            if ($value !== null) {
                $value = \json_decode((string) $value, true, 512, \JSON_THROW_ON_ERROR);

                if ($value === false || !isset($value[ConfigJsonField::STORAGE_KEY])) {
                    $value = null;
                } else {
                    $value = $value[ConfigJsonField::STORAGE_KEY];
                }
            }

            $inheritedValuePresent = \array_key_exists($key, $merged);
            $valueConsideredEmpty = !\is_bool($value) && empty($value);

            if ($inheritedValuePresent && $valueConsideredEmpty) {
                continue;
            }

            $merged[$key] = $value;
        }

        $merged = $this->symfonySystemConfigService->override($merged, $channelId, $inherit, false);

        $event = new SystemConfigDomainLoadedEvent($domain, $merged, $inherit, $channelId);
        $this->dispatcher->dispatch($event);

        return $event->getConfig();
    }

    /**
     * @param array<mixed>|bool|float|int|string|null $value
     */
    public function set(string $key, $value, ?string $channelId = null): void
    {
        $this->setMultiple([$key => $value], $channelId);
    }

    /**
     * @param array<string, array<mixed>|bool|float|int|string|null> $values
     */
    public function setMultiple(array $values, ?string $channelId = null): void
    {
        foreach ($values as $key => $value) {
            if ($this->symfonySystemConfigService->has($key)) {
                /**
                 * The administration setting pages are always sending the full configuration.
                 * This means when the user wants to change an allowed configuration, we also get the read-only configuration,
                 *
                 * Therefore, when the value of that field is the same as the statically configured one, we just drop that value and don't throw an exception
                 */
                if ($this->symfonySystemConfigService->get($key, $channelId) === $value) {
                    unset($values[$key]);
                    continue;
                }

                throw SystemConfigException::systemConfigKeyIsManagedBySystems($key);
            }
        }

        $beforeChangedEvent = new BeforeSystemConfigMultipleChangedEvent($values, $channelId);
        $this->dispatcher->dispatch($beforeChangedEvent);

        $values = $beforeChangedEvent->getConfig();

        $where = $channelId ? 'channel_id = :channelId' : 'channel_id IS NULL';

        $existingIds = $this->connection
            ->fetchAllKeyValue(
                'SELECT configuration_key, id FROM system_config WHERE ' . $where . ' and configuration_key IN (:configurationKeys)',
                [
                    'channelId' => $channelId ? Uuid::fromHexToBytes($channelId) : null,
                    'configurationKeys' => array_keys($values),
                ],
                [
                    'configurationKeys' => ArrayParameterType::STRING,
                ]
            );

        $toBeDeleted = [];
        $insertQueue = new MultiInsertQueryQueue($this->connection, 100, false, true);
        $events = [];

        foreach ($values as $key => $value) {
            $key = trim($key);
            $this->validate($key, $channelId);

            $event = new BeforeSystemConfigChangedEvent($key, $value, $channelId);
            $this->dispatcher->dispatch($event);

            // Use modified value provided by potential event subscribers.
            $value = $event->getValue();

            // On null value, delete the config
            if ($value === null) {
                $toBeDeleted[] = $key;

                $events[] = new SystemConfigChangedEvent($key, $value, $channelId);

                continue;
            }

            if (isset($existingIds[$key])) {
                $this->connection->update(
                    'system_config',
                    [
                        'configuration_value' => Json::encode(['_value' => $value]),
                        'updated_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT),
                    ],
                    [
                        'id' => $existingIds[$key],
                    ]
                );

                $events[] = new SystemConfigChangedEvent($key, $value, $channelId);

                continue;
            }

            $insertQueue->addInsert(
                'system_config',
                [
                    'id' => Uuid::randomBytes(),
                    'configuration_key' => $key,
                    'configuration_value' => Json::encode(['_value' => $value]),
                    'channel_id' => $channelId ? Uuid::fromHexToBytes($channelId) : null,
                    'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT),
                ],
            );

            $events[] = new SystemConfigChangedEvent($key, $value, $channelId);
        }

        // Delete all null values
        if (!empty($toBeDeleted)) {
            $qb = $this->connection
                ->createQueryBuilder()
                ->where('configuration_key IN (:keys)')
                ->setParameter('keys', $toBeDeleted, ArrayParameterType::STRING);

            if ($channelId) {
                $qb->andWhere('channel_id = :channelId')
                    ->setParameter('channelId', Uuid::fromHexToBytes($channelId));
            } else {
                $qb->andWhere('channel_id IS NULL');
            }

            $qb->delete('system_config')
                ->executeStatement();
        }

        $insertQueue->execute();

        // Dispatch events that the given values have been changed
        foreach ($events as $event) {
            $this->dispatcher->dispatch($event);
        }

        $this->dispatcher->dispatch(new SystemConfigMultipleChangedEvent($values, $channelId));
    }

    public function delete(string $key, ?string $channel = null): void
    {
        $this->setMultiple([$key => null], $channel);
    }

    /**
     * Fetches default values from bundle configuration and saves it to database
     */
    public function savePluginConfiguration(Bundle $bundle, bool $override = false): void
    {
        try {
            $config = $this->configReader->getConfigFromBundle($bundle);
        } catch (BundleConfigNotFoundException) {
            return;
        }

        $prefix = $bundle->getName() . '.config.';

        $this->saveConfig($config, $prefix, $override);
    }

    /**
     * @param array<mixed> $config
     */
    public function saveConfig(array $config, string $prefix, bool $override): void
    {
        $relevantSettings = $this->getDomain($prefix);

        foreach ($config as $card) {
            foreach ($card['elements'] as $element) {
                $key = $prefix . $element['name'];
                if (!isset($element['defaultValue'])) {
                    continue;
                }

                if ($override || !isset($relevantSettings[$key])) {
                    $this->set($key, $element['defaultValue']);
                }
            }
        }
    }

    public function deletePluginConfiguration(Bundle $bundle): void
    {
        try {
            $config = $this->configReader->getConfigFromBundle($bundle);
        } catch (BundleConfigNotFoundException) {
            return;
        }

        $this->deleteExtensionConfiguration($bundle->getName(), $config);
    }

    /**
     * @param array<mixed> $config
     */
    public function deleteExtensionConfiguration(string $extensionName, array $config): void
    {
        $prefix = $extensionName . '.config.';

        $configKeys = [];
        foreach ($config as $card) {
            foreach ($card['elements'] as $element) {
                $configKeys[] = $prefix . $element['name'];
            }
        }

        if (empty($configKeys)) {
            return;
        }

        // Get all sales channels that have the config keys
        $channelIds = $this->connection->fetchFirstColumn(
            'SELECT DISTINCT channel_id FROM system_config WHERE configuration_key IN (:keys) AND channel_id IS NOT NULL',
            ['keys' => $configKeys],
            ['keys' => ArrayParameterType::STRING]
        );

        $keysForDelete = array_fill_keys($configKeys, null);

        // Delete config keys for global scope
        $this->setMultiple($keysForDelete, null);

        // Delete overriden config keys for each sales channel
        foreach ($channelIds as $channelId) {
            $this->setMultiple($keysForDelete, Uuid::fromBytesToHex($channelId));
        }
    }

    public function reset(): void
    {
        $this->appMapping = null;
    }

    /**
     * @throws SystemConfigException
     * @throws InvalidUuidException
     */
    private function validate(string $key, ?string $channelId): void
    {
        $key = trim($key);
        if ($key === '') {
            throw SystemConfigException::invalidKey('key may not be empty');
        }
        if ($channelId) {
            // will throw if ID is invalid UUID
            Uuid::fromHexToBytes($channelId);
        }
    }

    /**
     * @return array<string, string>
     */
    private function getAppMapping(): array
    {
        if ($this->appMapping !== null) {
            return $this->appMapping;
        }

        /** @var array<string, string> $allKeyValue */
        $allKeyValue = $this->connection->fetchAllKeyValue('SELECT LOWER(HEX(id)), name FROM app');

        return $this->appMapping = $allKeyValue;
    }
}
