<?php declare(strict_types=1);

namespace HeyFrame\Tests\Integration\Core\Framework\App;

use HeyFrame\Core\Framework\App\AppCollection;
use HeyFrame\Core\Framework\App\Lifecycle\AppLifecycle;
use HeyFrame\Core\Framework\App\Lifecycle\Parameters\AppInstallParameters;
use HeyFrame\Core\Framework\App\Manifest\Manifest;
use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityRepository;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Criteria;
use HeyFrame\Core\System\CustomField\CustomFieldEntity;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @internal
 */
trait CustomFieldTypeTestBehaviour
{
    abstract protected static function getContainer(): ContainerInterface;

    protected function importCustomField(string $manifestPath): CustomFieldEntity
    {
        $manifest = Manifest::createFromXmlFile($manifestPath);

        $context = Context::createDefaultContext();
        $appLifecycle = static::getContainer()->get(AppLifecycle::class);
        $appLifecycle->install($manifest, new AppInstallParameters(), $context);

        /** @var EntityRepository<AppCollection> $appRepository */
        $appRepository = static::getContainer()->get('app.repository');
        $criteria = new Criteria();
        $criteria->addAssociation('customFieldSets.customFields');

        $apps = $appRepository->search($criteria, $context)->getEntities();

        static::assertCount(1, $apps);
        $app = $apps->first();
        static::assertNotNull($app);
        static::assertSame('SwagApp', $app->getName());

        $fieldSets = $app->getCustomFieldSets();
        static::assertNotNull($fieldSets);
        static::assertCount(1, $fieldSets);
        $customFieldSet = $fieldSets->first();
        static::assertNotNull($customFieldSet);
        static::assertSame('custom_field_test', $customFieldSet->getName());
        static::assertNotNull($customFieldSet->getCustomFields());

        static::assertCount(1, $customFieldSet->getCustomFields());

        $customField = $customFieldSet->getCustomFields()->first();
        static::assertNotNull($customField);

        return $customField;
    }
}
