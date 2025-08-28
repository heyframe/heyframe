<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Adapter\Messenger\Stamp;

use HeyFrame\Core\Framework\Log\Package;
use Symfony\Component\Messenger\Stamp\StampInterface;

#[Package('framework')]
readonly class SentAtStamp implements StampInterface
{
    private \DateTimeInterface $sentAt;

    public function __construct(?\DateTimeInterface $sentAt = null)
    {
        $this->sentAt = $sentAt ?? new \DateTimeImmutable();
    }

    public function getSentAt(): \DateTimeInterface
    {
        return $this->sentAt;
    }
}
