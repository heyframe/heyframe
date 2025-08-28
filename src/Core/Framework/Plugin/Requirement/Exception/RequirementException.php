<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Plugin\Requirement\Exception;

use HeyFrame\Core\Framework\HeyFrameHttpException;
use HeyFrame\Core\Framework\Log\Package;
use Symfony\Component\HttpFoundation\Response;

#[Package('framework')]
abstract class RequirementException extends HeyFrameHttpException
{
    public function getStatusCode(): int
    {
        return Response::HTTP_FAILED_DEPENDENCY;
    }
}
