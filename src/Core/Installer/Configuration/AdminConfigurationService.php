<?php declare(strict_types=1);

namespace HeyFrame\Core\Installer\Configuration;

use Doctrine\DBAL\Connection;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Installer\Controller\ShopConfigurationController;
use HeyFrame\Core\Maintenance\User\Service\UserProvisioner;

/**
 * @internal
 *
 * @phpstan-import-type AdminUser from ShopConfigurationController
 */
#[Package('framework')]
class AdminConfigurationService
{
    /**
     * @param AdminUser $user
     */
    public function createAdmin(array $user, Connection $connection): void
    {
        $userProvisioner = new UserProvisioner($connection);
        $userProvisioner->provision(
            $user['username'],
            $user['password'],
            [
                'firstName' => $user['firstName'],
                'lastName' => $user['lastName'],
                'email' => $user['email'],
            ]
        );
    }
}
