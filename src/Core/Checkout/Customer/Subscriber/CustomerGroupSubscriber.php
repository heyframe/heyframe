<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Customer\Subscriber;

use Cocur\Slugify\SlugifyInterface;
use HeyFrame\Core\Checkout\Customer\Aggregate\CustomerGroup\CustomerGroupCollection;
use HeyFrame\Core\Checkout\Customer\Aggregate\CustomerGroupTranslation\CustomerGroupTranslationCollection;
use HeyFrame\Core\Content\Seo\SeoUrl\SeoUrlCollection;
use HeyFrame\Core\Content\Seo\SeoUrlPersister;
use HeyFrame\Core\Defaults;
use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityRepository;
use HeyFrame\Core\Framework\DataAbstractionLayer\Event\EntityDeletedEvent;
use HeyFrame\Core\Framework\DataAbstractionLayer\Event\EntityWrittenEvent;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Criteria;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsAnyFilter;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Filter\NandFilter;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Language\LanguageCollection;
use HeyFrame\Core\System\Language\LanguageEntity;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @internal
 */
#[Package('checkout')]
class CustomerGroupSubscriber implements EventSubscriberInterface
{
    private const ROUTE_NAME = 'frontend.account.customer-group-registration.page';

    /**
     * @internal
     *
     * @param EntityRepository<CustomerGroupCollection> $customerGroupRepository
     * @param EntityRepository<SeoUrlCollection> $seoUrlRepository
     * @param EntityRepository<LanguageCollection> $languageRepository
     */
    public function __construct(
        private readonly EntityRepository $customerGroupRepository,
        private readonly EntityRepository $seoUrlRepository,
        private readonly EntityRepository $languageRepository,
        private readonly SeoUrlPersister $persister,
        private readonly SlugifyInterface $slugify
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            'customer_group_translation.written' => 'updatedCustomerGroup',
            'customer_group_registration_channels.written' => 'newChannelAddedToCustomerGroup',
            'customer_group_translation.deleted' => 'deleteCustomerGroup',
        ];
    }

    public function newChannelAddedToCustomerGroup(EntityWrittenEvent $event): void
    {
        $ids = [];

        foreach ($event->getWriteResults() as $writeResult) {
            /** @var array<string, string> $pk */
            $pk = $writeResult->getPrimaryKey();
            $ids[] = $pk['customerGroupId'];
        }

        if (\count($ids) === 0) {
            return;
        }

        $this->createUrls($ids, $event->getContext());
    }

    public function updatedCustomerGroup(EntityWrittenEvent $event): void
    {
        $ids = [];

        foreach ($event->getWriteResults() as $writeResult) {
            if ($writeResult->hasPayload('registrationTitle')) {
                /** @var array<string, string> $pk */
                $pk = $writeResult->getPrimaryKey();
                $ids[] = $pk['customerGroupId'];
            }
        }

        if (\count($ids) === 0) {
            return;
        }

        $this->createUrls($ids, $event->getContext());
    }

    public function deleteCustomerGroup(EntityDeletedEvent $event): void
    {
        $ids = [];

        foreach ($event->getWriteResults() as $writeResult) {
            /** @var array<string, string> $pk */
            $pk = $writeResult->getPrimaryKey();
            $ids[] = $pk['customerGroupId'];
        }

        if (\count($ids) === 0) {
            return;
        }

        $criteria = (new Criteria())
            ->addFilter(new EqualsAnyFilter('foreignKey', $ids))
            ->addFilter(new EqualsFilter('routeName', self::ROUTE_NAME));

        /** @var array<string> $ids */
        $ids = array_values($this->seoUrlRepository->searchIds($criteria, $event->getContext())->getIds());

        if (\count($ids) === 0) {
            return;
        }

        $this->seoUrlRepository->delete(array_map(fn (string $id) => ['id' => $id], $ids), $event->getContext());
    }

    /**
     * @param list<string> $ids
     */
    private function createUrls(array $ids, Context $context): void
    {
        $criteria = (new Criteria($ids))
            ->addFilter(new EqualsFilter('registrationActive', true))
            ->addAssociations(['registrationChannels.languages', 'translations']);

        $criteria->getAssociation('registrationChannels')
            ->addFilter(new NandFilter([new EqualsFilter('typeId', Defaults::CHANNEL_TYPE_API)]));

        $groups = $this->customerGroupRepository->search($criteria, $context)->getEntities();
        $buildUrls = [];

        foreach ($groups as $group) {
            if ($group->getRegistrationChannels() === null) {
                continue;
            }

            foreach ($group->getRegistrationChannels() as $registrationChannel) {
                if ($registrationChannel->getLanguages() === null) {
                    continue;
                }

                if ($registrationChannel->getTypeId() === Defaults::CHANNEL_TYPE_API) {
                    continue;
                }

                /** @var array<string> $languageIds */
                $languageIds = $registrationChannel->getLanguages()->getIds();
                $languageCriteria = new Criteria($languageIds);
                $languageCriteria->addFilter(new EqualsFilter('active', true));

                $languageCollection = $this->languageRepository->search($languageCriteria, $context)->getEntities();

                foreach ($languageIds as $languageId) {
                    $language = $languageCollection->get($languageId);
                    if (!$language) {
                        continue;
                    }

                    $title = $this->getTranslatedTitle($group->getTranslations(), $language);

                    if (empty($title)) {
                        continue;
                    }

                    if (!isset($buildUrls[$languageId])) {
                        $buildUrls[$languageId] = [
                            'urls' => [],
                            'channel' => $registrationChannel,
                        ];
                    }

                    $buildUrls[$languageId]['urls'][] = [
                        'channelId' => $registrationChannel->getId(),
                        'foreignKey' => $group->getId(),
                        'routeName' => self::ROUTE_NAME,
                        'pathInfo' => '/customer-group-registration/' . $group->getId(),
                        'isCanonical' => true,
                        'seoPathInfo' => '/' . $this->slugify->slugify($title),
                    ];
                }
            }
        }

        foreach ($buildUrls as $languageId => $config) {
            $context = new Context(
                $context->getSource(),
                $context->getRuleIds(),
                $context->getCurrencyId(),
                [$languageId]
            );

            $this->persister->updateSeoUrls(
                $context,
                self::ROUTE_NAME,
                array_column($config['urls'], 'foreignKey'),
                $config['urls'],
                $config['channel']
            );
        }
    }

    private function getTranslatedTitle(?CustomerGroupTranslationCollection $translations, LanguageEntity $language): string
    {
        if ($translations === null) {
            return '';
        }

        // Requested translation
        foreach ($translations as $translation) {
            if ($translation->getLanguageId() === $language->getId() && $translation->getRegistrationTitle() !== null) {
                return $translation->getRegistrationTitle();
            }
        }

        // Inherited translation
        foreach ($translations as $translation) {
            if ($translation->getLanguageId() === $language->getParentId() && $translation->getRegistrationTitle() !== null) {
                return $translation->getRegistrationTitle();
            }
        }

        // System Language
        foreach ($translations as $translation) {
            if ($translation->getLanguageId() === Defaults::LANGUAGE_SYSTEM && $translation->getRegistrationTitle() !== null) {
                return $translation->getRegistrationTitle();
            }
        }

        return '';
    }
}
