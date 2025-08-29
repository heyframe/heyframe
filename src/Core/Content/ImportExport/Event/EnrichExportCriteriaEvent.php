<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\ImportExport\Event;

use HeyFrame\Core\Content\ImportExport\Aggregate\ImportExportLog\ImportExportLogEntity;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Criteria;
use HeyFrame\Core\Framework\Log\Package;
use Symfony\Contracts\EventDispatcher\Event;

#[Package('fundamentals@after-sales')]
class EnrichExportCriteriaEvent extends Event
{
    public function __construct(
        private Criteria $criteria,
        private ImportExportLogEntity $logEntity
    ) {
    }

    public function getCriteria(): Criteria
    {
        return $this->criteria;
    }

    public function setCriteria(Criteria $criteria): void
    {
        $this->criteria = $criteria;
    }

    public function getLogEntity(): ImportExportLogEntity
    {
        return $this->logEntity;
    }

    public function setLogEntity(ImportExportLogEntity $logEntity): void
    {
        $this->logEntity = $logEntity;
    }
}
