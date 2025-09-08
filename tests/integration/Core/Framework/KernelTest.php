<?php declare(strict_types=1);

namespace HeyFrame\Tests\Integration\Core\Framework;

use Doctrine\DBAL\Connection;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Test\TestCaseBase\KernelTestBehaviour;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[Package('framework')]
class KernelTest extends TestCase
{
    use KernelTestBehaviour;

    public function testUTCIsAlwaysSetToDatabase(): void
    {
        $c = static::getContainer()->get(Connection::class);

        static::assertSame($c->fetchOne('SELECT @@session.time_zone'), '+08:00');
    }
}
