<?php

declare(strict_types=1);

namespace HeyFrame\Core\Framework\Util;

use HeyFrame\Core\Framework\Log\Package;

#[Package('framework')]
final class IOStreamHelper
{
    public static function writeError(string $message, ?\Throwable $error = null): void
    {
        $errorMessage = '';
        if ($error !== null) {
            $errorMessage = ' Error message: ' . $error->getMessage();
        }

        if (\defined('\STDERR')) {
            fwrite(\STDERR, $message . $errorMessage . \PHP_EOL);
        }
    }
}
