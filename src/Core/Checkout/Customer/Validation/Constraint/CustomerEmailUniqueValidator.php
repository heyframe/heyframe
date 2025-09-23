<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Customer\Validation\Constraint;

use Doctrine\DBAL\Connection;
use HeyFrame\Core\Checkout\Customer\CustomerException;
use HeyFrame\Core\Framework\Log\Package;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

#[Package('checkout')]
class CustomerEmailUniqueValidator extends ConstraintValidator
{
    /**
     * @internal
     */
    public function __construct(private readonly Connection $connection)
    {
    }

    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof CustomerEmailUnique) {
            throw CustomerException::unexpectedType($constraint, CustomerEmailUnique::class);
        }

        if ($value === null || $value === '') {
            return;
        }

        $query = $this->connection->createQueryBuilder();

        /** @var list<array{email: string, bound_channel_id: string|null}> $results */
        $results = $query
            ->select('email', 'LOWER(HEX(bound_channel_id)) as bound_channel_id')
            ->from('customer')
            ->where($query->expr()->eq('email', $query->createPositionalParameter($value)))
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

        $this->context->buildViolation($constraint->getMessage())
            ->setParameter('{{ email }}', $this->formatValue($value))
            ->setCode(CustomerEmailUnique::CUSTOMER_EMAIL_NOT_UNIQUE)
            ->addViolation();
    }
}
