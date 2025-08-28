<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Script\Api;

use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Struct\ArrayStruct;
use HeyFrame\Core\System\Channel\Api\ResponseFields;
use HeyFrame\Core\System\Channel\Api\StructEncoder;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

#[Package('framework')]
class ScriptResponseEncoder
{
    /**
     * @internal
     */
    public function __construct(private readonly StructEncoder $structEncoder)
    {
    }

    public function encodeToSymfonyResponse(ScriptResponse $scriptResponse, ResponseFields $responseFields, string $apiAlias): Response
    {
        $wrappedResponse = $scriptResponse->getInner();
        if ($wrappedResponse !== null) {
            return $wrappedResponse;
        }

        $data = $this->structEncoder->encode(new ArrayStruct($scriptResponse->getBody()->all(), $apiAlias), $responseFields);

        return new JsonResponse($data, $scriptResponse->getCode(), $scriptResponse->getHeaders());
    }
}
