<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\App\Lifecycle\Persister;

use HeyFrame\Core\Framework\App\Aggregate\AppScriptCondition\AppScriptConditionCollection;
use HeyFrame\Core\Framework\App\AppCollection;
use HeyFrame\Core\Framework\App\AppEntity;
use HeyFrame\Core\Framework\App\Lifecycle\ScriptFileReader;
use HeyFrame\Core\Framework\App\Manifest\Manifest;
use HeyFrame\Core\Framework\App\Manifest\Xml\CustomField\CustomFieldTypes\BoolField;
use HeyFrame\Core\Framework\App\Manifest\Xml\CustomField\CustomFieldTypes\CustomFieldType;
use HeyFrame\Core\Framework\App\Manifest\Xml\CustomField\CustomFieldTypes\FloatField;
use HeyFrame\Core\Framework\App\Manifest\Xml\CustomField\CustomFieldTypes\IntField;
use HeyFrame\Core\Framework\App\Manifest\Xml\CustomField\CustomFieldTypes\MediaSelectionField;
use HeyFrame\Core\Framework\App\Manifest\Xml\CustomField\CustomFieldTypes\MultiEntitySelectField;
use HeyFrame\Core\Framework\App\Manifest\Xml\CustomField\CustomFieldTypes\MultiSelectField;
use HeyFrame\Core\Framework\App\Manifest\Xml\CustomField\CustomFieldTypes\PriceField;
use HeyFrame\Core\Framework\App\Manifest\Xml\CustomField\CustomFieldTypes\SingleEntitySelectField;
use HeyFrame\Core\Framework\App\Manifest\Xml\CustomField\CustomFieldTypes\SingleSelectField;
use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityRepository;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Criteria;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Validation\Constraint\ArrayOfUuid;
use HeyFrame\Core\Framework\Validation\Constraint\Uuid;
use Symfony\Component\Validator\Constraints\All;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Type;

/**
 * @internal
 */
#[Package('framework')]
class RuleConditionPersister
{
    private const CONDITION_SCRIPT_DIR = '/rule-conditions/';

    /**
     * @param EntityRepository<AppScriptConditionCollection> $appScriptConditionRepository
     * @param EntityRepository<AppCollection> $appRepository
     */
    public function __construct(
        private readonly ScriptFileReader $scriptReader,
        private readonly EntityRepository $appScriptConditionRepository,
        private readonly EntityRepository $appRepository
    ) {
    }

    public function updateConditions(Manifest $manifest, string $appId, string $defaultLocale, Context $context): void
    {
        $app = $this->getAppWithExistingConditions($appId, $context);
        $existingRuleConditions = $app->getScriptConditions();
        \assert($existingRuleConditions !== null);

        $ruleConditions = $manifest->getRuleConditions();
        $ruleConditions = $ruleConditions !== null ? $ruleConditions->getRuleConditions() : [];

        $upserts = [];

        foreach ($ruleConditions as $ruleCondition) {
            $payload = $ruleCondition->toArray($defaultLocale);
            $payload['identifier'] = \sprintf('app\\%s_%s', $manifest->getMetadata()->getName(), $ruleCondition->getIdentifier());
            $payload['script'] = $this->scriptReader->getScriptContent(
                $app,
                self::CONDITION_SCRIPT_DIR . $ruleCondition->getScript(),
            );
            $payload['appId'] = $appId;
            $payload['active'] = $app->isActive();
            $payload['constraints'] = $this->hydrateConstraints($payload['constraints']);

            $existing = $existingRuleConditions->filterByProperty('identifier', $payload['identifier'])->first();

            if ($existing) {
                $existingRuleConditions->remove($existing->getId());
                $payload['id'] = $existing->getId();
            }

            $upserts[] = $payload;
        }

        if (!empty($upserts)) {
            $context->scope(Context::SYSTEM_SCOPE, function (Context $context) use ($upserts): void {
                $this->appScriptConditionRepository->upsert($upserts, $context);
            });
        }

        $this->deleteConditionScripts($existingRuleConditions, $context);
    }

    public function activateConditionScripts(string $appId, Context $context): void
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('appId', $appId));
        $criteria->addFilter(new EqualsFilter('active', false));

        /** @var array<string> $scripts */
        $scripts = $this->appScriptConditionRepository->searchIds($criteria, $context)->getIds();

        $updateSet = array_map(fn (string $id) => ['id' => $id, 'active' => true], $scripts);

        $this->appScriptConditionRepository->update($updateSet, $context);
    }

    public function deactivateConditionScripts(string $appId, Context $context): void
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('appId', $appId));
        $criteria->addFilter(new EqualsFilter('active', true));

        /** @var array<string> $scripts */
        $scripts = $this->appScriptConditionRepository->searchIds($criteria, $context)->getIds();

        $updateSet = array_map(fn (string $id) => ['id' => $id, 'active' => false], $scripts);

        $this->appScriptConditionRepository->update($updateSet, $context);
    }

    private function getAppWithExistingConditions(string $appId, Context $context): AppEntity
    {
        $criteria = new Criteria([$appId]);
        $criteria->addAssociation('scriptConditions');

        $app = $this->appRepository->search($criteria, $context)->getEntities()->first();
        \assert($app !== null);

        return $app;
    }

    private function deleteConditionScripts(AppScriptConditionCollection $toBeRemoved, Context $context): void
    {
        $ids = $toBeRemoved->getIds();

        if (!empty($ids)) {
            $ids = array_map(static fn (string $id): array => ['id' => $id], array_values($ids));

            $this->appScriptConditionRepository->delete($ids, $context);
        }
    }

    /**
     * @param list<CustomFieldType> $fields
     */
    private function hydrateConstraints(array $fields): string
    {
        $constraints = [];

        foreach ($fields as $field) {
            $constraints[$field->getName()] = [];

            if ($field->getRequired()) {
                $constraints[$field->getName()][] = new NotBlank();
            }

            if ($field instanceof PriceField) {
                continue;
            }

            if ($field instanceof BoolField) {
                $constraints[$field->getName()][] = new Type('bool');

                continue;
            }

            if ($field instanceof FloatField) {
                $constraints[$field->getName()][] = new Type('numeric');

                continue;
            }

            if ($field instanceof IntField) {
                $constraints[$field->getName()][] = new Type('int');

                continue;
            }

            if ($field instanceof MultiEntitySelectField) {
                $constraints[$field->getName()][] = new ArrayOfUuid();

                continue;
            }

            if ($field instanceof SingleEntitySelectField || $field instanceof MediaSelectionField) {
                $constraints[$field->getName()][] = new Uuid();

                continue;
            }

            if ($field instanceof MultiSelectField) {
                $constraints[$field->getName()][] = new All(constraints: new Choice(array_keys($field->getOptions())));

                continue;
            }

            if ($field instanceof SingleSelectField) {
                $constraints[$field->getName()][] = new Choice(array_keys($field->getOptions()));

                continue;
            }

            $constraints[$field->getName()][] = new Type('string');
        }

        return serialize($constraints);
    }
}
