<?php declare(strict_types=1); // @phpstan-ignore symplify.multipleClassLikeInFile

namespace HeyFrame\Core\Framework\Notification;

use HeyFrame\Administration\Notification\NotificationEntity;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityCollection;

if (class_exists(\HeyFrame\Administration\Notification\NotificationCollection::class)) {
    /**
     * @deprecated tag:v6.8.0 - reason:class-hierarchy-change - Will not extend from `\HeyFrame\Administration\Notification\NotificationCollection` and will instead extend directly from `\HeyFrame\Core\Framework\DataAbstractionLayer\EntityCollection`.
     */
    class NotificationCollection extends \HeyFrame\Administration\Notification\NotificationCollection
    {
        protected function getExpectedClass(): string
        {
            return NotificationEntity::class;
        }
    }
} else {
    /**
     * @extends EntityCollection<NotificationEntity>
     */
    class NotificationCollection extends EntityCollection
    {
        protected function getExpectedClass(): string
        {
            return NotificationEntity::class;
        }
    }
}
