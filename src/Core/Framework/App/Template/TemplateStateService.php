<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\App\Template;

use HeyFrame\Core\Framework\Adapter\Cache\CacheClearer;
use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityRepository;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Criteria;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use HeyFrame\Core\Framework\Log\Package;

/**
 * @internal only for use by the app-system
 */
#[Package('framework')]
class TemplateStateService
{
    /**
     * @param EntityRepository<TemplateCollection> $templateRepo
     */
    public function __construct(
        private readonly EntityRepository $templateRepo,
        private readonly CacheClearer $cacheClearer,
    ) {
    }

    public function activateAppTemplates(string $appId, Context $context): void
    {
        $this->updateAppTemplates($appId, $context, false, true);
    }

    public function deactivateAppTemplates(string $appId, Context $context): void
    {
        $this->updateAppTemplates($appId, $context, true, false);
    }

    private function updateAppTemplates(string $appId, Context $context, bool $currentActiveState, bool $newActiveState): void
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('appId', $appId));
        $criteria->addFilter(new EqualsFilter('active', $currentActiveState));

        /** @var array<string> $templates */
        $templates = $this->templateRepo->searchIds($criteria, $context)->getIds();

        $updateSet = array_map(fn (string $id) => ['id' => $id, 'active' => $newActiveState], $templates);

        $this->templateRepo->update($updateSet, $context);

        $this->cacheClearer->clearHttpCache();
    }
}
