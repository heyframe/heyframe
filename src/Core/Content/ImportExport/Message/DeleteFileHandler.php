<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\ImportExport\Message;

use HeyFrame\Core\Framework\Log\Package;
use League\Flysystem\FilesystemOperator;
use League\Flysystem\UnableToDeleteFile;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

/**
 * @internal
 */
#[AsMessageHandler]
#[Package('fundamentals@after-sales')]
final readonly class DeleteFileHandler
{
    /**
     * @internal
     */
    public function __construct(private FilesystemOperator $filesystem)
    {
    }

    public function __invoke(DeleteFileMessage $message): void
    {
        foreach ($message->getFiles() as $file) {
            try {
                $this->filesystem->delete($file);
            } catch (UnableToDeleteFile) {
                // ignore file is already deleted
            }
        }
    }
}
