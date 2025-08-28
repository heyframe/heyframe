<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Api\Context;

use HeyFrame\Core\Framework\Log\Package;
use Symfony\Component\Serializer\Attribute\DiscriminatorMap;

#[DiscriminatorMap(typeProperty: 'type', mapping: ['system' => SystemSource::class, 'sales-channel' => ChannelApiSource::class, 'admin-api' => AdminApiSource::class, 'shop-api' => ShopApiSource::class, 'admin-sales-channel-api' => AdminChannelApiSource::class])]
#[Package('framework')]
interface ContextSource
{
}
