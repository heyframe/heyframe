<?php declare(strict_types=1);

namespace HeyFrame\Core\Test\Integration\Builder\Customer;

use Doctrine\DBAL\Connection;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Test\TestCaseBase\KernelTestBehaviour;
use HeyFrame\Core\Framework\Uuid\Uuid;
use HeyFrame\Core\Test\Stub\Framework\IdsCollection;
use HeyFrame\Core\Test\TestBuilderTrait;
use HeyFrame\Core\Test\TestDefaults;

/**
 * @final
 * How to use:
 * $x = (new CustomerBuilder(new IdsCollection(), 'p1'))
 *          ->nickname('Max')
 *          ->group('standard')
 *          ->build();
 */
#[Package('checkout')]
class CustomerBuilder
{
    use KernelTestBehaviour;
    use TestBuilderTrait;

    public string $id;

    protected string $nickname = 'Mustermann';

    protected string $email = 'max@mustermann.com';

    protected string $customerGroupId;

    /**
     * @var array<string, mixed>
     */
    protected array $group = [];

    public function __construct(
        IdsCollection $ids,
        protected string $customerNumber,
        protected string $channelId = TestDefaults::CHANNEL,
        string $customerGroup = 'customer-group',
    ) {
        $this->ids = $ids;
        $this->id = $ids->create($customerNumber);

        $this->customerGroup($customerGroup);
    }

    public function customerNumber(string $customerNumber): self
    {
        $this->customerNumber = $customerNumber;

        return $this;
    }

    public function nickname(string $nickname): self
    {
        $this->nickname = $nickname;

        return $this;
    }

    public function customerGroup(string $key): self
    {
        $this->customerGroupId = $this->ids->get($key);
        $this->group = [
            'id' => $this->ids->get($key),
            'name' => $key,
        ];

        return $this;
    }

    private static function connection(): Connection
    {
        return self::getContainer()->get(Connection::class);
    }

    private function getCountry(): string
    {
        return self::connection()->fetchOne(
            'SELECT LOWER(HEX(country_id)) FROM channel_country WHERE channel_id = :id LIMIT 1',
            ['id' => Uuid::fromHexToBytes($this->channelId)]
        );
    }
}
