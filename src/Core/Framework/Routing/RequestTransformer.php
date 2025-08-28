<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Routing;

use HeyFrame\Core\Framework\Log\Package;
use Symfony\Component\HttpFoundation\Request;

#[Package('framework')]
class RequestTransformer implements RequestTransformerInterface
{
    public function transform(Request $request): Request
    {
        return $request;
    }

    /**
     * @return array<string, mixed>
     */
    public function extractInheritableAttributes(Request $sourceRequest): array
    {
        return [];
    }
}
