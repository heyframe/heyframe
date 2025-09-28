<?php declare(strict_types=1);

namespace HeyFrame\Frontend\Controller;

use HeyFrame\Core\Checkout\Cart\Cart;
use HeyFrame\Core\Checkout\Cart\Error\ErrorRoute;
use HeyFrame\Core\Content\Media\MediaUrlPlaceholderHandlerInterface;
use HeyFrame\Core\Framework\Adapter\Twig\TemplateFinder;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Routing\RequestTransformerInterface;
use HeyFrame\Core\PlatformRequest;
use HeyFrame\Core\Profiling\Profiler;
use HeyFrame\Core\System\SystemConfig\SystemConfigService;
use HeyFrame\Frontend\Controller\Exception\FrontendException;
use HeyFrame\Frontend\Event\FrontendRedirectEvent;
use HeyFrame\Frontend\Event\FrontendRenderEvent;
use HeyFrame\Frontend\Framework\Routing\RequestTransformer;
use HeyFrame\Frontend\Framework\Routing\Router;
use HeyFrame\Frontend\Framework\Twig\Extension\IconCacheTwigFilter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\FlashBagAwareSessionInterface;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

#[Package('framework')]
abstract class FrontendController extends AbstractController
{
    public const SUCCESS = 'success';
    public const DANGER = 'danger';
    public const INFO = 'info';
    public const WARNING = 'warning';

    public static function getSubscribedServices(): array
    {
        $services = parent::getSubscribedServices();

        $services['twig'] = Environment::class;
        $services['event_dispatcher'] = EventDispatcherInterface::class;
        $services[SystemConfigService::class] = SystemConfigService::class;
        $services[TemplateFinder::class] = TemplateFinder::class;
        $services[MediaUrlPlaceholderHandlerInterface::class] = MediaUrlPlaceholderHandlerInterface::class;
        $services['translator'] = TranslatorInterface::class;
        $services[RequestTransformerInterface::class] = RequestTransformerInterface::class;

        return $services;
    }

    /**
     * @param array<string, mixed> $parameters
     */
    protected function renderFrontend(string $view, array $parameters = []): Response
    {
        $request = $this->container->get('request_stack')->getCurrentRequest();

        if ($request === null) {
            throw FrontendException::noRequestProvided();
        }

        $channelContext = $request->attributes->get(PlatformRequest::ATTRIBUTE_CHANNEL_CONTEXT_OBJECT);

        $event = new FrontendRenderEvent($view, $parameters, $request, $channelContext);

        $this->container->get('event_dispatcher')->dispatch($event);

        $iconCacheEnabled = $this->getSystemConfigService()->get('core.storefrontSettings.iconCache') ?? true;

        if ($iconCacheEnabled) {
            IconCacheTwigFilter::enable();
        }

        $response = Profiler::trace('twig-rendering', fn () => $this->render($view, $event->getParameters(), new Response()));

        if ($iconCacheEnabled) {
            IconCacheTwigFilter::disable();
        }

        $host = $request->attributes->get(RequestTransformer::STOREFRONT_URL);

        $mediaUrlReplacer = $this->container->get(MediaUrlPlaceholderHandlerInterface::class);
        $content = $response->getContent();

        if ($content !== false) {
            $content = $mediaUrlReplacer->replace($content);

            $response->setContent($content);
        }

        $response->headers->set('Content-Type', 'text/html');

        return $response;
    }

    /**
     * @param array<string, mixed> $parameters
     */
    protected function trans(string $snippet, array $parameters = []): string
    {
        return $this->container
            ->get('translator')
            ->trans($snippet, $parameters);
    }

    protected function createActionResponse(Request $request): Response
    {
        if ($request->get('redirectTo') || $request->get('redirectTo') === '') {
            $params = $this->decodeParam($request, 'redirectParameters');

            $redirectTo = $request->get('redirectTo');

            if ($redirectTo && \is_string($redirectTo)) {
                return $this->redirectToRoute($redirectTo, $params);
            }

            return $this->redirectToRoute('frontend.home.page', $params);
        }

        if ($request->get('forwardTo')) {
            $params = $this->decodeParam($request, 'forwardParameters');

            return $this->forwardToRoute($request->get('forwardTo'), [], $params);
        }

        return new Response();
    }

    /**
     * @param array<string, mixed> $attributes
     * @param array<string, mixed> $routeParameters
     */
    protected function forwardToRoute(string $routeName, array $attributes = [], array $routeParameters = []): Response
    {
        $router = $this->container->get('router');

        try {
            $url = $this->generateUrl($routeName, $routeParameters, Router::PATH_INFO);
        } catch (RouteNotFoundException $e) {
            throw FrontendException::routeNotFound($routeName, $e);
        }

        // for the route matching the request method is set to "GET" because
        // this method is not ought to be used as a post passthrough
        // rather it shall return templates or redirects to display results of the request ahead
        $method = $router->getContext()->getMethod();
        $router->getContext()->setMethod(Request::METHOD_GET);

        $route = $router->match($url);
        $router->getContext()->setMethod($method);

        $request = $this->container->get('request_stack')->getCurrentRequest();

        if ($request === null) {
            throw FrontendException::noRequestProvided();
        }

        $attributes = array_merge(
            $this->container->get(RequestTransformerInterface::class)->extractInheritableAttributes($request),
            $route,
            $attributes,
            // in the case of virtual urls (localhost/de) we need to skip the request transformer matching, otherwise the virtual url (/de) is stripped out, and we cannot find any sales channel
            // so we set the `skip-transformer` attribute, which is checked in the HttpKernel before the request transformer is set
            ['_route_params' => $routeParameters, 'sw-skip-transformer' => true]
        );

        return $this->forward($route['_controller'], $attributes, $routeParameters);
    }

    /**
     * @return array<string, mixed>
     */
    protected function decodeParam(Request $request, string $param): array
    {
        $params = $request->get($param);

        if (\is_string($params)) {
            $params = json_decode($params, true);
        }

        if (empty($params) || \is_numeric($params)) {
            $params = [];
        }

        return $params;
    }

    protected function addCartErrors(Cart $cart, ?\Closure $filter = null): void
    {
        $errors = $cart->getErrors();
        if ($filter !== null) {
            $errors = $errors->filter($filter);
        }

        $groups = [
            'info' => $errors->getNotices(),
            'warning' => $errors->getWarnings(),
            'danger' => $errors->getErrors(),
        ];

        $request = $this->container->get('request_stack')->getMainRequest();
        $exists = [];

        if ($request && $request->hasSession() && $request->getSession() instanceof FlashBagAwareSessionInterface) {
            $exists = $request->getSession()->getFlashBag()->peekAll();
        }

        $flat = [];
        foreach ($exists as $messages) {
            $flat = array_merge($flat, $messages);
        }

        foreach ($groups as $type => $errorGroup) {
            foreach ($errorGroup as $error) {
                $parameters = [];

                foreach ($error->getParameters() as $key => $value) {
                    $parameters['%' . $key . '%'] = $value;
                }

                if ($error->getRoute() instanceof ErrorRoute) {
                    $parameters['%url%'] = $this->generateUrl(
                        $error->getRoute()->getKey(),
                        $error->getRoute()->getParams()
                    );
                }

                $translatedMessage = $this->trans('checkout.' . $error->getMessageKey(), $parameters);
                $error->setTranslatedMessage($translatedMessage);

                if (\in_array($translatedMessage, $flat, true)) {
                    continue;
                }

                $this->addFlash($type, $translatedMessage);
            }
        }
    }

    /**
     * @param array<string, mixed> $parameters
     */
    protected function redirectToRoute(string $route, array $parameters = [], int $status = Response::HTTP_FOUND): RedirectResponse
    {
        $event = new FrontendRedirectEvent($route, $parameters, $status);
        $this->container->get('event_dispatcher')->dispatch($event);

        try {
            return parent::redirectToRoute($event->getRoute(), $event->getParameters(), $event->getStatus());
        } catch (RouteNotFoundException $e) {
            throw FrontendException::routeNotFound($route, $e);
        }
    }

    /**
     * @param array<string, mixed> $parameters
     */
    protected function renderView(string $view, array $parameters = []): string
    {
        $view = $this->getTemplateFinder()->find($view);

        try {
            return $this->container->get('twig')->render($view, $parameters);
        } catch (LoaderError|RuntimeError|SyntaxError $e) {
            throw FrontendException::renderViewException($view, $e, $parameters);
        }
    }

    /**
     * @param array<string, mixed> $parameters
     */
    protected function render(string $view, array $parameters = [], ?Response $response = null): Response
    {
        $content = $this->renderView($view, $parameters);

        $response ??= new Response();

        $response->setContent($content);

        return $response;
    }

    protected function getTemplateFinder(): TemplateFinder
    {
        return $this->container->get(TemplateFinder::class);
    }

    protected function getSystemConfigService(): SystemConfigService
    {
        return $this->container->get(SystemConfigService::class);
    }
}
