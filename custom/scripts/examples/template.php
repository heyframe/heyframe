<?php declare(strict_types=1);

namespace Scripts\Examples;

require_once __DIR__ . '/base-script.php';

$env = 'prod'; // by default, kernel gets booted in dev

$kernel = require __DIR__ . '/../boot/boot.php';

class Main extends BaseScript
{
    public function run(): void
    {
    }
}

(new Main($kernel))->run();
