<?php declare(strict_types=1);

namespace HeyFrame\Core\System\Dict;

use HeyFrame\Core\Framework\HttpException;
use HeyFrame\Core\Framework\Log\Package;
use Symfony\Component\HttpFoundation\Response;

/**
 * @internal
 */
#[Package('framework')]
class DictException extends HttpException
{
    public const DICT_NOT_FOUND = 'SYSTEM__DICT_NOT_FOUND';

    public static function dictNotFound(string $key): self
    {
        return new self(
            Response::HTTP_NOT_FOUND,
            self::DICT_NOT_FOUND,
            'Dict for key "{{ $key }}" not found.',
            ['key' => $key]
        );
    }
}
