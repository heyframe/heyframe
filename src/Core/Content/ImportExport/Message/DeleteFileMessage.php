<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\ImportExport\Message;

use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\MessageQueue\AsyncMessageInterface;

#[Package('fundamentals@after-sales')]
class DeleteFileMessage implements AsyncMessageInterface
{
    private array $files = [];

    public function getFiles(): array
    {
        return $this->files;
    }

    public function setFiles(array $files): void
    {
        $this->files = $files;
    }
}
