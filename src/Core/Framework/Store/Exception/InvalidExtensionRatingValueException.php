<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Store\Exception;

use HeyFrame\Core\Framework\HeyFrameHttpException;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Store\Struct\ReviewStruct;
use Symfony\Component\HttpFoundation\Response;

#[Package('checkout')]
class InvalidExtensionRatingValueException extends HeyFrameHttpException
{
    public function __construct(
        int $rating,
        array $parameters = [],
        ?\Throwable $e = null
    ) {
        $parameters['rating'] = $rating;
        $parameters['maxRating'] = ReviewStruct::MAX_RATING;
        $parameters['minRating'] = ReviewStruct::MIN_RATING;

        parent::__construct('Invalid rating value {{rating}}. The value must correspond to a number in the interval from {{minRating}} to {{maxRating}}.', $parameters, $e);
    }

    public function getErrorCode(): string
    {
        return 'FRAMEWORK__INVALID_EXTENSION_RATING_VALUE';
    }

    public function getStatusCode(): int
    {
        return Response::HTTP_BAD_REQUEST;
    }
}
