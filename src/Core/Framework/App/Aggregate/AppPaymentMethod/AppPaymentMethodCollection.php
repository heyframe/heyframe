<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\App\Aggregate\AppPaymentMethod;

use HeyFrame\Core\Framework\DataAbstractionLayer\EntityCollection;
use HeyFrame\Core\Framework\Log\Package;

/**
 * @internal only for use by the app-system
 *
 * @extends EntityCollection<AppPaymentMethodEntity>
 */
#[Package('framework')]
class AppPaymentMethodCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return AppPaymentMethodEntity::class;
    }
}
