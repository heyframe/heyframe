<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\ImportExport\Event;

use HeyFrame\Core\Content\ImportExport\Struct\Config;
use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\Log\Package;
use Symfony\Contracts\EventDispatcher\Event;

#[Package('fundamentals@after-sales')]
class ImportExportBeforeImportRowEvent extends Event
{
    public function __construct(
        private array $row,
        private readonly Config $config,
        private readonly Context $context
    ) {
    }

    public function getRow(): array
    {
        return $this->row;
    }

    public function setRow(array $row): void
    {
        $this->row = $row;
    }

    public function getConfig(): Config
    {
        return $this->config;
    }

    public function getContext(): Context
    {
        return $this->context;
    }
}
