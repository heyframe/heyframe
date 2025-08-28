<?php declare(strict_types=1);

namespace HeyFrame\Core\System\Snippet\Struct;

use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Struct\Struct;

#[Package('discovery')]
class SnippetValidationStruct extends Struct
{
    public function __construct(
        public readonly MissingSnippetCollection $missingSnippets,
        public readonly InvalidPluralizationCollection $invalidPluralization,
    ) {
    }
}
