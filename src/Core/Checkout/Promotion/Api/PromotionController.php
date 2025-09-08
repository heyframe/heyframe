<?php declare(strict_types=1);

namespace HeyFrame\Core\Checkout\Promotion\Api;

use HeyFrame\Core\Checkout\Promotion\PromotionException;
use HeyFrame\Core\Checkout\Promotion\Util\PromotionCodeService;
use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Routing\ApiRouteScope;
use HeyFrame\Core\PlatformRequest;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(defaults: [PlatformRequest::ATTRIBUTE_ROUTE_SCOPE => [ApiRouteScope::ID]])]
#[Package('checkout')]
class PromotionController extends AbstractController
{
    /**
     * @internal
     */
    public function __construct(private readonly PromotionCodeService $codeService)
    {
    }

    #[Route(path: '/api/_action/promotion/codes/generate-fixed', name: 'api.action.promotion.codes.generate-fixed', defaults: ['_acl' => ['promotion.editor']], methods: ['GET'])]
    public function generateFixedCode(): Response
    {
        return new JsonResponse($this->codeService->getFixedCode());
    }

    #[Route(path: '/api/_action/promotion/codes/generate-individual', name: 'api.action.promotion.codes.generate-individual', defaults: ['_acl' => ['promotion.editor']], methods: ['GET'])]
    public function generateIndividualCodes(Request $request): Response
    {
        $codePattern = (string) $request->query->get('codePattern');
        if ($codePattern === '') {
            throw PromotionException::missingRequestParameter('codePattern');
        }
        $amount = $request->query->getInt('amount');

        return new JsonResponse($this->codeService->generateIndividualCodes($codePattern, $amount));
    }

    #[Route(path: '/api/_action/promotion/codes/replace-individual', name: 'api.action.promotion.codes.replace-individual', defaults: ['_acl' => ['promotion.editor']], methods: ['PATCH'])]
    public function replaceIndividualCodes(Request $request, Context $context): Response
    {
        $promotionId = (string) $request->request->get('promotionId');
        $codePattern = (string) $request->request->get('codePattern');
        $amount = $request->request->getInt('amount');

        $this->codeService->replaceIndividualCodes($promotionId, $codePattern, $amount, $context);

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    #[Route(path: '/api/_action/promotion/codes/add-individual', name: 'api.action.promotion.codes.add-individual', defaults: ['_acl' => ['promotion.editor']], methods: ['POST'])]
    public function addIndividualCodes(Request $request, Context $context): Response
    {
        $promotionId = (string) $request->request->get('promotionId');
        $amount = $request->request->getInt('amount');

        $this->codeService->addIndividualCodes($promotionId, $amount, $context);

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    #[Route(path: '/api/_action/promotion/codes/preview', name: 'api.action.promotion.codes.preview', defaults: ['_acl' => ['promotion.editor']], methods: ['GET'])]
    public function getCodePreview(Request $request): Response
    {
        $codePattern = (string) $request->query->get('codePattern');
        if ($codePattern === '') {
            throw PromotionException::missingRequestParameter('codePattern');
        }

        return new JsonResponse($this->codeService->getPreview($codePattern));
    }
}
