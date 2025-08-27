<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Adapter\Filesystem\Exception;

use HeyFrame\Core\Framework\Adapter\AdapterException;
use Symfony\Component\HttpFoundation\Response;

class DuplicateFilesystemFactoryException extends AdapterException
{
    public function __construct(string $type)
    {
        parent::__construct(
            Response::HTTP_INTERNAL_SERVER_ERROR,
            'FRAMEWORK__DUPLICATE_FILESYSTEM_FACTORY',
            'The type of factory "{{ type }}" must be unique.',
            ['type' => $type]
        );
    }
}
