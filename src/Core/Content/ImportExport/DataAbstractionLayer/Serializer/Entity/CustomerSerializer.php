<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\ImportExport\DataAbstractionLayer\Serializer\Entity;

use HeyFrame\Core\Checkout\Customer\Aggregate\CustomerGroup\CustomerGroupCollection;
use HeyFrame\Core\Checkout\Customer\CustomerDefinition;
use HeyFrame\Core\Content\ImportExport\Struct\Config;
use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityDefinition;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityRepository;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Criteria;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelCollection;
use Symfony\Contracts\Service\ResetInterface;

#[Package('fundamentals@after-sales')]
class CustomerSerializer extends EntitySerializer implements ResetInterface
{
    /**
     * @internal
     *
     * @param EntityRepository<CustomerGroupCollection> $customerGroupRepository
     * @param EntityRepository<ChannelCollection> $channelRepository
     * @param array<string, string|null> $cacheCustomerGroups
     * @param array<string, string|null> $cacheChannels
     */
    public function __construct(
        private readonly EntityRepository $customerGroupRepository,
        private readonly EntityRepository $channelRepository,
        private array $cacheCustomerGroups = [],
        private array $cacheChannels = [],
    ) {
    }

    public function deserialize(Config $config, EntityDefinition $definition, $entity)
    {
        $entity = \is_array($entity) ? $entity : iterator_to_array($entity);

        $deserialized = parent::deserialize($config, $definition, $entity);

        $deserialized = \is_array($deserialized) ? $deserialized : iterator_to_array($deserialized);

        $context = Context::createDefaultContext();

        if (!isset($deserialized['groupId']) && isset($entity['group'])) {
            $name = $entity['group']['translations']['DEFAULT']['name'] ?? null;
            $id = $entity['group']['id'] ?? $this->getCustomerGroupId($name, $context);

            if ($id) {
                $deserialized['group']['id'] = $id;
            }
        }

        if (!isset($deserialized['channelId']) && isset($entity['channel'])) {
            $name = $entity['channel']['translations']['DEFAULT']['name'] ?? null;
            $id = $entity['channel']['id'] ?? $this->getChannelId($name, $context);

            if ($id) {
                $deserialized['channel']['id'] = $id;
            }
        }

        if (!isset($deserialized['boundChannelId']) && isset($entity['boundChannel'])) {
            $name = $entity['boundChannel']['translations']['DEFAULT']['name'] ?? null;
            $id = $entity['boundChannel']['id'] ?? $this->getChannelId($name, $context);

            if ($id) {
                $deserialized['boundChannel']['id'] = $id;
            }
        }

        yield from $deserialized;
    }

    public function supports(string $entity): bool
    {
        return $entity === CustomerDefinition::ENTITY_NAME;
    }

    public function reset(): void
    {
        $this->cacheCustomerGroups = [];
        $this->cacheChannels = [];
    }

    private function getCustomerGroupId(?string $name, Context $context): ?string
    {
        if (!$name) {
            return null;
        }

        if (\array_key_exists($name, $this->cacheCustomerGroups)) {
            return $this->cacheCustomerGroups[$name];
        }

        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('name', $name));
        $this->cacheCustomerGroups[$name] = $this->customerGroupRepository->searchIds(
            $criteria,
            $context
        )->firstId();

        return $this->cacheCustomerGroups[$name];
    }

    private function getChannelId(?string $name, Context $context): ?string
    {
        if (!$name) {
            return null;
        }

        if (\array_key_exists($name, $this->cacheChannels)) {
            return $this->cacheChannels[$name];
        }

        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('name', $name));

        $this->cacheChannels[$name] = $this->channelRepository->searchIds(
            $criteria,
            $context
        )->firstId();

        return $this->cacheChannels[$name];
    }
}
