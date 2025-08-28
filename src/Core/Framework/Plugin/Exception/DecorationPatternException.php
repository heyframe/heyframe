<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Plugin\Exception;

use HeyFrame\Core\Framework\HeyFrameHttpException;
use HeyFrame\Core\Framework\Log\Package;
use Symfony\Component\HttpFoundation\Response;

#[Package('framework')]
class DecorationPatternException extends HeyFrameHttpException
{
    public function __construct(protected string $class)
    {
        parent::__construct(\sprintf(
            'The getDecorated() function of core class %s cannot be used. This class is the base class.',
            $class
        ));
    }

    public function getErrorCode(): string
    {
        return (string) Response::HTTP_INTERNAL_SERVER_ERROR;
    }
}
