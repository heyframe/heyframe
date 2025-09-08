<?php declare(strict_types=1); // @phpstan-ignore symplify.multipleClassLikeInFile

namespace HeyFrame\Core\Framework\Notification;

use HeyFrame\Core\Framework\DataAbstractionLayer\EntityCollection;
use HeyFrame\Core\Framework\Log\Package;

/**
 * @extends EntityCollection<NotificationEntity>
 */
#[Package('framework')]
class NotificationCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return NotificationEntity::class;
    }
}
