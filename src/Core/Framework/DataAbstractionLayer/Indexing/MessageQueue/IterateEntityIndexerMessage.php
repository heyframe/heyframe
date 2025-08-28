<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\DataAbstractionLayer\Indexing\MessageQueue;

use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\MessageQueue\AsyncMessageInterface;
use HeyFrame\Core\Framework\MessageQueue\DeduplicatableMessageInterface;
use HeyFrame\Core\Framework\Util\Hasher;

#[Package('framework')]
class IterateEntityIndexerMessage implements AsyncMessageInterface, DeduplicatableMessageInterface
{
    /**
     * @internal
     *
     * @param array{offset: int|null}|null $offset
     * @param array<string> $skip
     */
    public function __construct(
        protected string $indexer,
        protected ?array $offset,
        protected array $skip = []
    ) {
    }

    public function getIndexer(): string
    {
        return $this->indexer;
    }

    /**
     * @return array{offset: int|null}|null
     */
    public function getOffset(): ?array
    {
        return $this->offset;
    }

    /**
     * @param array{offset: int|null}|null $offset
     */
    public function setOffset(?array $offset): void
    {
        $this->offset = $offset;
    }

    /**
     * @return array<string>
     */
    public function getSkip(): array
    {
        return $this->skip;
    }

    /**
     * @experimental stableVersion:v6.8.0 feature:DEDUPLICATABLE_MESSAGES
     */
    public function deduplicationId(): ?string
    {
        $sortedSkip = $this->skip;
        sort($sortedSkip);

        $sortedOffset = $this->offset;
        if (\is_array($sortedOffset)) {
            ksort($sortedOffset);
        }

        $data = json_encode([
            $this->indexer,
            $sortedOffset,
            $sortedSkip,
        ]);

        if ($data === false) {
            return null;
        }

        return Hasher::hash($data);
    }
}
