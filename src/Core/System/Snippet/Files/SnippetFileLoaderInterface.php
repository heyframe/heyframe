<?php declare(strict_types=1);

namespace HeyFrame\Core\System\Snippet\Files;

use HeyFrame\Core\Framework\Log\Package;

#[Package('discovery')]
interface SnippetFileLoaderInterface
{
    public function loadSnippetFilesIntoCollection(SnippetFileCollection $snippetFileCollection): void;
}
