<?php

declare(strict_types=1);

namespace HeyFrame\Core\Content\Media\Extension;

use HeyFrame\Core\Content\Media\MediaEntity;
use HeyFrame\Core\Framework\DataAbstractionLayer\PartialEntity;
use HeyFrame\Core\Framework\Extensions\Extension;
use HeyFrame\Core\Framework\Log\Package;

/**
 * @extends Extension<string|null>
 *
 * @codeCoverageIgnore
 */
#[Package('discovery')]
final class ResolveRemoteThumbnailUrlExtension extends Extension
{
    public const NAME = 'remote_thumbnail_url.resolve';

    /**
     * @internal shopware owns the __constructor, but the properties are public API
     */
    public function __construct(
        public string $mediaUrl,
        public string $width,
        public string $height,
        public string $pattern,
        public MediaEntity|PartialEntity $mediaEntity,
    ) {
    }
}
