<?php declare(strict_types=1);

namespace SwagTestExecuteComposerCommands;

use HeyFrame\Core\Framework\Plugin;

class SwagTestExecuteComposerCommands extends Plugin
{
    public function executeComposerCommands(): bool
    {
        return true;
    }
}
