<?php declare(strict_types=1);

namespace HeyFrame\Core\System\StateMachine\Exception;

use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\StateMachine\StateMachineException;
use Symfony\Component\HttpFoundation\Response;

#[Package('checkout')]
class UnnecessaryTransitionException extends StateMachineException
{
    public function __construct(string $transition)
    {
        parent::__construct(
            Response::HTTP_BAD_REQUEST,
            self::UNNECESSARY_TRANSITION,
            'The transition "{{ transition }}" is unnecessary, already on desired state.',
            ['transition' => $transition]
        );
    }
}
