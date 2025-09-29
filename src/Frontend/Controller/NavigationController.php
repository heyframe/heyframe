<?php declare(strict_types=1);

namespace HeyFrame\Frontend\Controller;

use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\PlatformRequest;
use HeyFrame\Core\System\Channel\ChannelContext;
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
        $cmsPage = $this->getNewCmsStructure();

        return $this->renderFrontend(
            '@Frontend/frontend/page/content/index.html.twig',
            [
                'page' => $page,
                'cmsPage' => $cmsPage,
            ]
        );
    }

    /**
     * TODO: Remove after final CMS structure is implemented.
     */
    private static function getNewCmsStructure()
    {
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
            ],
        ];
    }
}
