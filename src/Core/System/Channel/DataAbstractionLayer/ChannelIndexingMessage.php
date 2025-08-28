<?php declare(strict_types=1);

namespace HeyFrame\Core\System\Channel\DataAbstractionLayer;

use HeyFrame\Core\Framework\DataAbstractionLayer\Indexing\EntityIndexingMessage;
use HeyFrame\Core\Framework\Log\Package;

#[Package('discovery')]
class ChannelIndexingMessage extends EntityIndexingMessage
{
}
