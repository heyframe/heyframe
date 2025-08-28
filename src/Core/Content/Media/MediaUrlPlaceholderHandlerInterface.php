<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Media;

use HeyFrame\Core\Framework\Log\Package;

#[Package('discovery')]
interface MediaUrlPlaceholderHandlerInterface
{
    public function replace(string $content): string;
}
