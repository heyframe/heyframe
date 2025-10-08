<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\ContentSystem\Element\Loader;

use HeyFrame\Core\Content\ContentSystem\ContentSystemException;
use HeyFrame\Core\Content\ContentSystem\Element\Definition\SchemaValidator;
use HeyFrame\Core\Content\ContentSystem\Element\Schema\ElementTypeSchema;
use HeyFrame\Core\Framework\Log\Package;

/**
 * Loads element type definitions from JSON schema files.
 *
 * Discovers element type schemas from:
 * - HeyFrame core (src/Core/Content/ContentSystem/Element/Types/)
 * - Plugins (custom/plugins/.../Resources/config/content-elements/)
 * - Apps (custom/apps/.../Resources/content-elements/)
 *
 * @internal
 */
#[Package('discovery')]
class ElementTypeLoader
{
    public function __construct(
        private readonly SchemaValidator $validator
    ) {
    }

    /**
     * Load element type schema from a JSON file.
     *
     * @throws ContentSystemException If file cannot be loaded or schema is invalid
     */
    public function loadFromFile(string $file): ElementTypeSchema
    {
        if (!is_file($file)) {
            throw ContentSystemException::elementSchemaLoadFailed(
                $file,
                'File does not exist'
            );
        }

        if (!is_readable($file)) {
            throw ContentSystemException::elementSchemaLoadFailed(
                $file,
                'File is not readable'
            );
        }

        $content = file_get_contents($file);
        if ($content === false) {
            throw ContentSystemException::elementSchemaLoadFailed(
                $file,
                'Failed to read file'
            );
        }

        try {
            $data = json_decode($content, true, 512, \JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            throw ContentSystemException::elementSchemaLoadFailed(
                $file,
                'Invalid JSON: ' . $e->getMessage(),
                $e
            );
        }

        if (!\is_array($data)) {
            throw ContentSystemException::elementSchemaLoadFailed(
                $file,
                'Schema must be a JSON object'
            );
        }

        // Validate schema
        $this->validator->validate($data);

        // Create schema object
        return ElementTypeSchema::fromArray($data);
    }

    /**
     * Load multiple element type schemas from a directory.
     *
     * @return array<ElementTypeSchema>
     */
    public function loadFromDirectory(string $directory): array
    {
        if (!is_dir($directory)) {
            return [];
        }

        $schemas = [];
        $files = glob($directory . '/*.json');

        if ($files === false) {
            return [];
        }

        foreach ($files as $file) {
            try {
                $schema = $this->loadFromFile($file);
                $schemas[$schema->getName()] = $schema;
            } catch (\Throwable $e) {
                // Fail fast on invalid schema files - re-throw if already ContentSystemException
                if ($e instanceof ContentSystemException) {
                    throw $e;
                }

                throw ContentSystemException::elementSchemaLoadFailed(
                    $file,
                    $e->getMessage(),
                    $e
                );
            }
        }

        return $schemas;
    }

    /**
     * Load element type schemas from multiple directories.
     *
     * @param array<string> $directories
     *
     * @return array<ElementTypeSchema>
     */
    public function loadFromDirectories(array $directories): array
    {
        $schemas = [];

        foreach ($directories as $directory) {
            $directorySchemas = $this->loadFromDirectory($directory);
            $schemas = array_merge($schemas, $directorySchemas);
        }

        return $schemas;
    }
}
