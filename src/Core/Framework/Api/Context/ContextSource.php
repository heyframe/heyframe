<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Api\Context;

use Symfony\Component\Serializer\Attribute\DiscriminatorMap;

#[DiscriminatorMap(typeProperty: 'type', mapping: ['system' => SystemSource::class, 'channel' => ChannelApiSource::class, 'admin-api' => AdminApiSource::class,  'admin-channel-api' => AdminChannelApiSource::class])]
interface ContextSource
{
}
