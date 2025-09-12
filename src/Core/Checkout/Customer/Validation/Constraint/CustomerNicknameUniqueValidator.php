<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Customer\Validation\Constraint;

use Doctrine\DBAL\Connection;
use HeyFrame\Core\Checkout\Customer\CustomerException;
use HeyFrame\Core\Framework\Log\Package;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

#[Package('checkout')]
class CustomerNicknameUniqueValidator extends ConstraintValidator
{
    /**
     * @internal
     */
    public function __construct(private readonly Connection $connection)
    {
    }

    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof CustomerNicknameUnique) {
            throw CustomerException::unexpectedType($constraint, CustomerNicknameUnique::class);
        }

        if ($value === null || $value === '') {
            return;
        }

        $query = $this->connection->createQueryBuilder();

        /** @var array{nickname: string, bound_channel_id: string|null}[] $results */
        $results = $query
            ->select('nickname', 'LOWER(HEX(bound_channel_id)) as bound_channel_id')
            ->from('customer')
            ->where($query->expr()->eq('nickname', $query->createPositionalParameter($value)))
            ->executeQuery()
            ->fetchAllAssociative();

        $results = \array_filter($results, static function (array $entry) use ($constraint) {

            if ($entry['bound_channel_id'] === null) {
                return true;
            }

            if ($entry['bound_channel_id'] !== $constraint->getChannelContext()->getChannelId()) {
                return false;
            }

            return true;
        });

        // If we don't have anything, skip
        if ($results === []) {
            return;
        }

        $this->context->buildViolation($constraint->message)
            ->setParameter('{{ nickname }}', $this->formatValue($value))
            ->setCode(CustomerNicknameUnique::CUSTOMER_USERNAME_NOT_UNIQUE)
            ->addViolation();
    }
}
