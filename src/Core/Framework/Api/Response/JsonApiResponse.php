<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Api\Response;

use HeyFrame\Core\Framework\Log\Package;
use Symfony\Component\HttpFoundation\JsonResponse;

#[Package('framework')]
class JsonApiResponse extends JsonResponse
{
    protected function update(): static
    {
        parent::update();

        $this->headers->set('Content-Type', 'application/vnd.api+json');

        return $this;
    }
}
