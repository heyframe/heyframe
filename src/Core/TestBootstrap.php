<?php declare(strict_types=1);

namespace HeyFrame\Core;

require __DIR__ . '/TestBootstrapper.php';

(new TestBootstrapper())
    ->setPlatformEmbedded(false)
    ->setEnableCommercial()
    ->bootstrap();
