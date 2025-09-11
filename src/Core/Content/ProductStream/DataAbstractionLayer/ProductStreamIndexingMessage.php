<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\ProductStream\DataAbstractionLayer;

use HeyFrame\Core\Framework\DataAbstractionLayer\Indexing\EntityIndexingMessage;
use HeyFrame\Core\Framework\Log\Package;

#[Package('inventory')]
class ProductStreamIndexingMessage extends EntityIndexingMessage
{
}
