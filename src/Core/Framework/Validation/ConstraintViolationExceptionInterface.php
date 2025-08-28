<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Validation;

use HeyFrame\Core\Framework\HeyFrameException;
use HeyFrame\Core\Framework\Log\Package;
use Symfony\Component\Validator\ConstraintViolationList;

#[Package('framework')]
interface ConstraintViolationExceptionInterface extends HeyFrameException
{
    public function getViolations(): ConstraintViolationList;
}
