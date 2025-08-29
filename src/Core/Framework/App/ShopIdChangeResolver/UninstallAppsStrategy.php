<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\App\ShopIdChangeResolver;

use HeyFrame\Core\Framework\App\AppCollection;
use HeyFrame\Core\Framework\App\Event\AppDeactivatedEvent;
use HeyFrame\Core\Framework\App\ShopId\ShopIdProvider;
use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityRepository;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Criteria;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Plugin\Exception\DecorationPatternException;

/**
 * @internal
 *
 * Resolver used when apps should be uninstalled
 * and the shopId should be regenerated, meaning the old shops and old apps work like before
 * apps in the current installation will be uninstalled without informing them about that (as they still run on the old installation)
 */
#[Package('framework')]
class UninstallAppsStrategy extends AbstractShopIdChangeStrategy
{
    final public const STRATEGY_NAME = 'uninstall-apps';

    /**
     * @param EntityRepository<AppCollection> $appRepository
     */
    public function __construct(
        private readonly EntityRepository $appRepository,
        private readonly ShopIdProvider $shopIdProvider,
    ) {
    }

    public function getDecorated(): AbstractShopIdChangeStrategy
    {
        throw new DecorationPatternException(self::class);
    }

    public function getName(): string
    {
        return self::STRATEGY_NAME;
    }

    public function getDescription(): string
    {
        return 'This is typically the right option if you have made a copy of your shop (e.g. a staging or testing environment of a production shop) and you don’t want to use the apps in this copy. HeyFrame will delete the apps without notifying the app servers. A new shop identifier will be generated and your shop will identify as a new shop.';
    }

    public function resolve(Context $context): void
    {
        $this->shopIdProvider->deleteShopId();

        foreach ($this->appRepository->search(new Criteria(), $context)->getEntities() as $app) {
            $this->appRepository->delete([['id' => $app->getId()]], $context);
        }
    }
}
