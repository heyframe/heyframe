<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Api\Exception;

use HeyFrame\Core\Framework\HeyFrameHttpException;
use HeyFrame\Core\Framework\Log\Package;
use Symfony\Component\HttpFoundation\Response;

#[Package('framework')]
class ExpectationFailedException extends HeyFrameHttpException
{
    /**
     * @param array<string> $fails
     */
    public function __construct(private readonly array $fails)
    {
        parent::__construct('API Expectations failed', []);
    }

    /**
     * @return array<string> $failedExpectations
     */
    public function getParameters(): array
    {
        return $this->fails;
    }

    public function getErrorCode(): string
    {
        return 'FRAMEWORK__API_EXPECTATION_FAILED';
    }

    public function getStatusCode(): int
    {
        return Response::HTTP_EXPECTATION_FAILED;
    }
}
