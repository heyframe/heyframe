<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\App\InstanceIdChangeResolver;

use HeyFrame\Core\Framework\App\AppEntity;
use HeyFrame\Core\Framework\App\Exception\InstanceIdChangeSuggestedException;
use HeyFrame\Core\Framework\App\InstanceId\InstanceIdProvider;
use HeyFrame\Core\Framework\App\Lifecycle\Registration\AppRegistrationService;
use HeyFrame\Core\Framework\App\Manifest\Manifest;
use HeyFrame\Core\Framework\App\Source\SourceResolver;
use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityRepository;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Plugin\Exception\DecorationPatternException;

/**
 * @internal
 *
 * Resolver used when shop is moved from one URL to another
 * and the instanceId (and the data in the app backends associated with it) should be kept
 *
 * Will run through the registration process for all apps again
 * with the new appUrl so the apps can save the new URL and generate new Secrets
 * that way communication from the old shop to the app backend will be blocked in the future
 */
#[Package('framework')]
class MoveShopPermanentlyStrategy extends AbstractInstanceIdChangeStrategy
{
    final public const STRATEGY_NAME = 'move-shop-permanently';

    public function __construct(
        SourceResolver $sourceResolver,
        EntityRepository $appRepository,
        AppRegistrationService $registrationService,
        private readonly InstanceIdProvider $instanceIdProvider
    ) {
        parent::__construct($sourceResolver, $appRepository, $registrationService);
    }

    public function getDecorated(): AbstractInstanceIdChangeStrategy
    {
        throw new DecorationPatternException(self::class);
    }

    public function getName(): string
    {
        return self::STRATEGY_NAME;
    }

    public function getDescription(): string
    {
        return 'This is typically the right option if you have permanently moved your shop to a different infrastructure or new environment. HeyFrame will notify apps (i.e. re-register at the app servers) using the same shop identifier and apps remain installed. Your shop will identify as the same shop as before.';
    }

    public function resolve(Context $context): void
    {
        try {
            $this->instanceIdProvider->getInstanceId();

            // no resolution needed
            return;
        } catch (InstanceIdChangeSuggestedException $e) {
            $this->instanceIdProvider->regenerateAndSetInstanceId($e->instanceId->id);
        }

        $this->forEachInstalledApp($context, function (Manifest $manifest, AppEntity $app, Context $context): void {
            $this->reRegisterApp($manifest, $app, $context);
        });
    }
}
