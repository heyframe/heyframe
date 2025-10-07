<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\ContentSystem\Resolver;

use HeyFrame\Core\Content\ContentSystem\Routing\Struct\RouteMatchResult;
use HeyFrame\Core\Framework\Log\Package;

#[Package('discovery')]
class ParameterExtractor
{
    /**
     * Extracts parameters from the route match that need resolution vs pass-through.
     *
     * @return array{resolution: array<string, array{placeholder: string, resolution: array<string, mixed>, value: mixed}>, passthrough: array<string, mixed>}
     */
    public function extract(RouteMatchResult $match): array
    {
        $route = $match->getRoute();
        $parameters = $match->getParameters();
        $parameterBinding = $route->getParameterBinding();

        $resolution = [];
        $passthrough = [];

        foreach ($parameterBinding as $paramName => $config) {
            $value = $parameters[$paramName] ?? null;

            if ($value === null) {
                continue;
            }

            $placeholder = $config['placeholder'] ?? $paramName;

            // Check if this parameter requires entity resolution
            if (isset($config['resolution'])) {
                $resolution[$paramName] = [
                    'placeholder' => $placeholder,
                    'resolution' => $config['resolution'],
                    'value' => $value,
                ];
            } else {
                // Pass-through parameter (no resolution needed)
                $passthrough[$placeholder] = $value;
            }
        }

        return [
            'resolution' => $resolution,
            'passthrough' => $passthrough,
        ];
    }
}
