<?php declare(strict_types=1);

namespace HeyFrame\Core\System\User\Service;

use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityRepository;
use HeyFrame\Core\Framework\DataAbstractionLayer\Exception\InconsistentCriteriaIdsException;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Criteria;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Filter\MultiFilter;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Filter\NotEqualsFilter;
use HeyFrame\Core\System\User\UserCollection;

class UserValidationService
{
    /**
     * @internal
     *
     * @param EntityRepository<UserCollection> $userRepo
     */
    public function __construct(private readonly EntityRepository $userRepo)
    {
    }

    /**
     * @throws InconsistentCriteriaIdsException
     */
    public function checkEmailUnique(string $userEmail, string $userId, Context $context): bool
    {
        $criteria = new Criteria();

        $criteria->addFilter(
            new MultiFilter(
                'AND',
                [
                    new EqualsFilter('email', $userEmail),
                    new NotEqualsFilter('id', $userId),
                ]
            )
        );

        return $this->userRepo->searchIds($criteria, $context)->getTotal() === 0;
    }

    /**
     * @throws InconsistentCriteriaIdsException
     */
    public function checkUsernameUnique(string $userUsername, string $userId, Context $context): bool
    {
        $criteria = new Criteria();

        $criteria->addFilter(
            new MultiFilter(
                'AND',
                [
                    new EqualsFilter('username', $userUsername),
                    new NotEqualsFilter('id', $userId),
                ]
            )
        );

        return $this->userRepo->searchIds($criteria, $context)->getTotal() === 0;
    }
}
