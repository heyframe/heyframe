<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Store\Authentication;

use HeyFrame\Core\Framework\Api\Context\AdminApiSource;
use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityRepository;
use HeyFrame\Core\Framework\DataAbstractionLayer\Exception\EntityNotFoundException;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Criteria;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\User\UserCollection;
use HeyFrame\Core\System\User\UserDefinition;

/**
 * @internal
 */
#[Package('checkout')]
class LocaleProvider
{
    /**
     * @param EntityRepository<UserCollection> $userRepository
     */
    public function __construct(private readonly EntityRepository $userRepository)
    {
    }

    public function getLocaleFromContext(Context $context): string
    {
        if (!$context->getSource() instanceof AdminApiSource) {
            return 'zh-CN';
        }

        /** @var AdminApiSource $source */
        $source = $context->getSource();

        if ($source->getUserId() === null) {
            return 'zh-CN';
        }

        $criteria = new Criteria([$source->getUserId()]);
        $criteria->addAssociation('locale');

        $user = $this->userRepository->search($criteria, $context)->getEntities()->first();

        if ($user === null) {
            throw new EntityNotFoundException(UserDefinition::ENTITY_NAME, $source->getUserId());
        }

        $locale = $user->getLocale();
        \assert($locale !== null);

        return $locale->getCode();
    }
}
