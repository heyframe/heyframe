<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\DataAbstractionLayer\Contract;

interface IdAware
{
    public function getId(): string;
}
