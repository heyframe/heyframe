<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Media;

interface MediaUrlPlaceholderHandlerInterface
{
    public function replace(string $content): string;
}
