<?php declare(strict_types=1);

namespace HeyFrame\Core\System\CustomField\Api;

use HeyFrame\Core\Framework\DataAbstractionLayer\DefinitionInstanceRegistry;
use HeyFrame\Core\Framework\DataAbstractionLayer\EntityTranslationDefinition;
use HeyFrame\Core\Framework\DataAbstractionLayer\Field\CustomFields;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Routing\ApiRouteScope;
use HeyFrame\Core\PlatformRequest;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route(defaults: [PlatformRequest::ATTRIBUTE_ROUTE_SCOPE => [ApiRouteScope::ID]])]
#[Package('framework')]
class CustomFieldSetActionController extends AbstractController
{
    /**
     * @internal
     */
    public function __construct(private readonly DefinitionInstanceRegistry $definitionRegistry)
    {
    }

    #[Route(path: '/api/_action/attribute-set/relations', name: 'api.action.attribute-set.get-relations', methods: ['GET'])]
    public function getAvailableRelations(): JsonResponse
    {
        $definitions = $this->definitionRegistry->getDefinitions();

        $entityNames = [];
        foreach ($definitions as $definition) {
            if (\count($definition->getFields()->filterInstance(CustomFields::class)) === 0) {
                continue;
            }
            if ($definition instanceof EntityTranslationDefinition) {
                $definition = $definition->getParentDefinition();
            }
            $entityNames[] = $definition->getEntityName();
        }
        sort($entityNames);

        return new JsonResponse($entityNames);
    }
}
