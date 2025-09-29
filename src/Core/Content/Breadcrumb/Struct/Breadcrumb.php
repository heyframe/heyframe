<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Breadcrumb\Struct;

use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Struct\Struct;

#[Package('inventory')]
class Breadcrumb extends Struct
{
    /**
     * @param array<string, mixed> $translated
     * @param list<array<string, string>> $seoUrls
     */
    public function __construct(
        public string $name,
        public string $navigationId = '',
        public string $type = '',
        public array $translated = [],
        public string $path = '',
        public array $seoUrls = []
    ) {
    }

    public function getApiAlias(): string
    {
        return 'breadcrumb';
    }
}
