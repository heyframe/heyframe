<?php declare(strict_types=1);

namespace HeyFrame\Tests\Unit\Core\Maintenance\User\Command;

use HeyFrame\Core\Framework\Api\Acl\Admin\Role\AclRoleEntity;
use HeyFrame\Core\Framework\Api\Acl\Role\AclRoleCollection;
use HeyFrame\Core\Framework\Uuid\Uuid;
use HeyFrame\Core\Maintenance\MaintenanceException;
use HeyFrame\Core\Maintenance\User\Command\UserListCommand;
use HeyFrame\Core\System\User\UserCollection;
use HeyFrame\Core\System\User\UserEntity;
use HeyFrame\Core\Test\Stub\DataAbstractionLayer\StaticEntityRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * @internal
 */
#[CoversClass(UserListCommand::class)]
class UserListCommandTest extends TestCase
{
    public function testWithNoUsers(): void
    {
        /** @var StaticEntityRepository<UserCollection> $repo */
        $repo = new StaticEntityRepository([new UserCollection()]);

        $command = new UserListCommand($repo);
        $commandTester = new CommandTester($command);
        $commandTester->execute([]);

        $commandTester->assertCommandIsSuccessful();

        $output = $commandTester->getDisplay();

        static::assertStringContainsString('There are no users', $output);
    }

    public function testWithUsers(): void
    {
        $commandTester = $this->prepareCommandTester();
        $commandTester->execute([]);

        $commandTester->assertCommandIsSuccessful();

        $output = $commandTester->getDisplay();

        static::assertStringContainsString('Guy', $output);
        static::assertStringContainsString('Jen', $output);
    }

    public function testAclRolesNotLoadedException(): void
    {
        $userName = 'guy';
        $userId = Uuid::randomHex();
        /** @var StaticEntityRepository<UserCollection> $repo */
        $repo = new StaticEntityRepository([
            new UserCollection([
                $this->createUser('guy@heyframe.com', $userName, 'Guy', id: $userId),
            ]),
        ]);

        $command = new UserListCommand($repo);
        $commandTester = new CommandTester($command);

        $this->expectExceptionObject(MaintenanceException::aclRolesNotLoaded($userId, $userName));
        $commandTester->execute([]);
    }

    public function testWithJson(): void
    {
        $commandTester = $this->prepareCommandTester();
        $commandTester->execute(['--json' => true]);

        $commandTester->assertCommandIsSuccessful();

        $output = $commandTester->getDisplay();

        static::assertTrue(json_validate($output));
        static::assertStringContainsString('Guy', $output);
        static::assertStringContainsString('Jen', $output);
    }

    private function prepareCommandTester(): CommandTester
    {
        /** @var StaticEntityRepository<UserCollection> $repo */
        $repo = new StaticEntityRepository([
            new UserCollection([
                $this->createUser('guy@heyframe.com', 'guy', 'Guy', true),
                $this->createUser('jen@heyframe.com', 'jen', 'Jen', false, ['Jen', 'CS']),
            ]),
        ]);

        $command = new UserListCommand($repo);

        return new CommandTester($command);
    }

    /**
     * @param array<string> $roles
     */
    private function createUser(
        string $email,
        string $username,
        string $name,
        bool $isAdmin = false,
        ?array $roles = null,
        ?string $id = null,
    ): UserEntity {
        $user = new UserEntity();
        $user->setId($id ?? Uuid::randomHex());
        $user->setEmail($email);
        $user->setActive(true);
        $user->setUsername($username);
        $user->setName($name);
        $user->setAdmin($isAdmin);
        $user->setCreatedAt(new \DateTime());

        if ($roles) {
            $user->setAclRoles(new AclRoleCollection(array_map(static function (string $role): AclRoleEntity {
                $aclRole = new AclRoleEntity();
                $aclRole->setId(Uuid::randomHex());
                $aclRole->setName($role);

                return $aclRole;
            }, $roles)));
        }

        return $user;
    }
}
