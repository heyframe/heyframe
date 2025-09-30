<?php declare(strict_types=1);

namespace HeyFrame\Frontend\Controller;

use HeyFrame\Core\Content\Media\MediaEntity;
use HeyFrame\Core\Framework\DataAbstractionLayer\Search\Criteria;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\PlatformRequest;
use HeyFrame\Core\System\Channel\ChannelContext;
use HeyFrame\Core\System\Channel\Entity\ChannelRepository;
use HeyFrame\Frontend\Framework\Routing\FrontendRouteScope;
use HeyFrame\Frontend\Page\Navigation\NavigationPageLoaderInterface;
use HeyFrame\Frontend\Pagelet\Footer\FooterPageletLoaderInterface;
use HeyFrame\Frontend\Pagelet\Header\HeaderPageletLoaderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * @internal
 * Do not use direct or indirect repository calls in a controller. Always use a store-api route to get or put data
 */
#[Route(defaults: [PlatformRequest::ATTRIBUTE_ROUTE_SCOPE => [FrontendRouteScope::ID]])]
#[Package('discovery')]
class NavigationController extends FrontendController
{
    public function __construct(
        private readonly NavigationPageLoaderInterface $navigationPageLoader,
        private readonly HeaderPageletLoaderInterface $headerLoader,
        private readonly FooterPageletLoaderInterface $footerLoader,
        private readonly ChannelRepository $productRepository,
    ) {
    }

    #[Route(
        path: '/',
        name: 'frontend.home.page',
        defaults: ['_httpCache' => true],
        methods: ['GET'],
    )]
    public function home(Request $request, ChannelContext $context): Response
    {
        $page = $this->navigationPageLoader->load($request, $context);
        $cmsPage = $this->getNewCmsStructure($context);

        return $this->renderFrontend(
            '@Frontend/frontend/page/content/index.html.twig',
            [
                'page' => $page,
                'cmsPage' => $cmsPage,
            ]
        );
    }

    #[Route(
        path: '/_esi/global/header',
        name: 'frontend.header',
        defaults: ['XmlHttpRequest' => true, '_httpCache' => true, '_esi' => true],
        methods: ['GET'],
    )]
    public function header(Request $request, ChannelContext $context): Response
    {
        $header = $this->headerLoader->load($request, $context);

        return $this->renderFrontend('@Frontend/frontend/layout/header.html.twig', [
            'header' => $header,
            'headerParameters' => $request->get('headerParameters') ?? [],
        ]);
    }

    #[Route(
        path: '/_esi/global/footer',
        name: 'frontend.footer',
        defaults: ['XmlHttpRequest' => true, '_httpCache' => true, '_esi' => true],
        methods: ['GET'],
    )]
    public function footer(Request $request, ChannelContext $context): Response
    {
        $footer = $this->footerLoader->load($request, $context);

        return $this->renderFrontend('@Frontend/frontend/layout/footer.html.twig', [
            'footer' => $footer,
            'footerParameters' => $request->get('footerParameters') ?? [],
        ]);
    }

    /**
     * TODO: Remove after final CMS structure is implemented.
     */
    private function getNewCmsStructure(ChannelContext $context)
    {
        $criteria = new Criteria(['11dc680240b04f469ccba354cbf0b967']);
        $criteria->addAssociation('media.media');
        $criteria->addAssociation('cover.media');
        $product = $this->productRepository->search($criteria, $context)->getEntities()->first();

        $elements[] = $product;
        $media = new MediaEntity();
        $media->setId('123');
        $media->setMimeType('image/webp');
        $media->setFileExtension('webp');
        $media->setFileSize(1203165);
        $media->setFileName('inspire-connect-riseup-mood.webp');
        $media->setTitle('Test');
        $media->setAlt('Test');
        $media->setUrl('https://www.shopware.com/media/pages/products/shopping-experiences/inspire-connect-riseup-mood.webp');

        return [
            'id' => '123',
            'name' => 'Home',
            'elements' => [
                [
                    'id' => '123',
                    'component' => 'Sw:Grid:Container',
                    'properties' => [
                        'columns' => '2',
                        'columnsLg' => '1',
                        'gap' => '24',
                        'align' => 'start',
                        'alignContent' => 'start',
                        'justify' => 'stretch',
                        'justifyContent' => 'stretch',
                    ],
                    'slots' => [
                        'column-1' => [
                            [
                                'id' => '123',
                                'component' => 'Sw:Grid:Column',
                                'properties' => [
                                    'start' => null,
                                    'span' => null,
                                ],
                                'slots' => [
                                    'content' => [
                                        [
                                            'id' => '123',
                                            'component' => 'Sw:Media:Image',
                                            'properties' => [
                                                'media' => $media,
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        'column-2' => [
                            [
                                'id' => 'ABC',
                                'component' => 'Sw:Grid:Column',
                                'properties' => [
                                    'start' => null,
                                    'span' => null,
                                ],
                                'slots' => [
                                    'default' => [
                                        [
                                            'id' => '123',
                                            'component' => 'Sw:Content:Text',
                                            'properties' => [
                                                'text' => '<h1>Lorem 2 ipsum dolor sit amet.</h1><p>Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet.</p>',
                                            ],
                                        ],
                                        [
                                            'id' => '123',
                                            'component' => 'Sw:Alert',
                                            'properties' => [
                                                'text' => 'Hello World',
                                            ],
                                            'slots' => [
                                                'content' => [
                                                    [
                                                        'id' => '123',
                                                        'component' => 'Sw:Button',
                                                        'properties' => [
                                                            'text' => 'Click Me!',
                                                        ],
                                                    ],
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                [
                    'id' => '123',
                    'component' => 'Sw:Product:Listing',
                    'properties' => [
                        'listing' => [
                            'elements' => $elements,
                        ],
                    ],
                ],
            ],
        ];
    }
}
