<?php declare(strict_types=1);

namespace HeyFrame\Core\System\NumberRange\Exception;

use HeyFrame\Core\Framework\HeyFrameHttpException;
use HeyFrame\Core\Framework\Log\Package;
use Symfony\Component\HttpFoundation\Response;

#[Package('framework')]
class NoConfigurationException extends HeyFrameHttpException
{
    public function __construct(
        string $entityName,
        ?string $channelId = null
    ) {
        parent::__construct(
            'No number range configuration found for entity "{{ entity }}" with sales channel "{{ channelId }}".',
            ['entity' => $entityName, 'channelId' => $channelId]
        );
    }

    public function getStatusCode(): int
    {
        return Response::HTTP_BAD_REQUEST;
    }

    public function getErrorCode(): string
    {
        return 'FRAMEWORK__NO_NUMBER_RANGE_CONFIGURATION';
    }
}
