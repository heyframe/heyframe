<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Media\Message;

/**
 * @codeCoverageIgnore
 */
class UpdateThumbnailsMessage extends GenerateThumbnailsMessage
{
    private bool $strict = false;

    public function isStrict(): bool
    {
        return $this->strict;
    }

    public function setStrict(bool $isStrict): void
    {
        $this->strict = $isStrict;
    }
}
