<?php
declare(strict_types=1);

namespace HeyFrame\Core\Content\Media\Extension;

use HeyFrame\Core\Framework\Extensions\Extension;
use HeyFrame\Core\Framework\Log\Package;

/**
 * @extends Extension<string>
 *
 * @codeCoverageIgnore
 */
#[Package('discovery')]
final class ResolveRemoteThumbnailUrlExtension extends Extension
{
    public const NAME = 'remote_thumbnail_url.resolve';

    /**
     * @internal heyframe owns the __constructor, but the properties are public API
     */
    public function __construct(
        public string $mediaUrl,
        public string $mediaPath,
        public string $width,
        public string $height,
        public string $pattern,
        public ?\DateTimeInterface $mediaUpdatedAt
    ) {
    }
}
