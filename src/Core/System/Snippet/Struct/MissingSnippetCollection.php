<?php declare(strict_types=1);

namespace HeyFrame\Core\System\Snippet\Struct;

use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Struct\Collection;

/**
 * @extends Collection<MissingSnippetStruct>
 */
#[Package('discovery')]
class MissingSnippetCollection extends Collection
{
}
