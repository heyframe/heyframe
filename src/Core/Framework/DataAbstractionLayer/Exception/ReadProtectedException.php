<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\DataAbstractionLayer\Exception;

use HeyFrame\Core\Framework\HeyFrameHttpException;
use HeyFrame\Core\Framework\Log\Package;
use Symfony\Component\HttpFoundation\Response;

#[Package('framework')]
class ReadProtectedException extends HeyFrameHttpException
{
    public function __construct(
        string $field,
        string $scope
    ) {
        parent::__construct(
            'The field/association "{{ field }}" is read protected for your scope "{{ scope }}"',
            [
                'field' => $field,
                'scope' => $scope,
            ]
        );
    }

    public function getErrorCode(): string
    {
        return 'FRAMEWORK__READ_PROTECTED';
    }

    public function getStatusCode(): int
    {
        return Response::HTTP_FORBIDDEN;
    }
}
