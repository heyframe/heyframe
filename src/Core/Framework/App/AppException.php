<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\App;

use HeyFrame\Core\Framework\HttpException;

class AppException extends HttpException
{
    public const XML_PARSE_ERROR = 'FRAMEWORK_APP__XML_PARSE_ERROR';
}
