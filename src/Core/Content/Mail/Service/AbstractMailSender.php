<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\Mail\Service;

use HeyFrame\Core\Content\Mail\MailException;
use HeyFrame\Core\Framework\Log\Package;
use Symfony\Component\Mime\Email;

#[Package('after-sales')]
abstract class AbstractMailSender
{
    abstract public function getDecorated(): AbstractMailSender;

    /**
     * @throws MailException
     */
    abstract public function send(Email $email): void;
}
