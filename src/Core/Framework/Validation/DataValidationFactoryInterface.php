<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Validation;

use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelContext;

#[Package('framework')]
interface DataValidationFactoryInterface
{
    public function create(ChannelContext $context): DataValidationDefinition;

    public function update(ChannelContext $context): DataValidationDefinition;
}
