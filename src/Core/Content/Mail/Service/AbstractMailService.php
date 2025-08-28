<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Mail\Service;

use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\Log\Package;
use Symfony\Component\Mime\Email;

#[Package('after-sales')]
abstract class AbstractMailService
{
    abstract public function getDecorated(): AbstractMailService;

    /**
     * @param array<string, mixed> $data
     * @param array<string, mixed> $templateData
     */
    abstract public function send(array $data, Context $context, array $templateData = []): ?Email;
}
