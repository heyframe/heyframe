<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\ContentSystem\Compilation;

use HeyFrame\Core\Content\ContentSystem\Resolver\Struct\ResolvedData;
use HeyFrame\Core\Framework\Log\Package;

#[Package('discovery')]
class PlaceholderFillerService
{
    /**
     * Fills placeholders in layout structure with resolved values.
     * Replaces {{variable}} with actual values from ResolvedData.
     *
     * @param array<string, mixed> $structure
     *
     * @return array<string, mixed>
     */
    public function fill(array $structure, ResolvedData $resolvedData): array
    {
        $json = \json_encode($structure);

        if ($json === false) {
            return $structure;
        }

        // Get all values (entity IDs + parameters)
        $values = $resolvedData->getValues();

        // Replace placeholders
        foreach ($values as $key => $value) {
            // Only replace scalar values (string, int, float, bool)
            if (\is_scalar($value)) {
                $placeholder = '{{' . $key . '}}';
                $json = \str_replace($placeholder, (string) $value, $json);
            }
        }

        $filled = \json_decode($json, true);

        return $filled ?? $structure;
    }
}
