<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\DataAbstractionLayer\Indexing;

use HeyFrame\Core\Framework\Log\Package;

#[Package('framework')]
abstract class SynchronousPostUpdateIndexer extends PostUpdateIndexer
{
}
