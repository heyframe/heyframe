<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Api\Context;

use HeyFrame\Core\Framework\Log\Package;

#[Package('framework')]
class ShopApiSource extends ChannelApiSource
{
    public string $type = 'shop-api';
}
