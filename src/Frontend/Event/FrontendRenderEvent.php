<?php declare(strict_types=1);

namespace HeyFrame\Frontend\Event;

use HeyFrame\Core\Framework\Context;
use HeyFrame\Core\Framework\Event\HeyFrameChannelEvent;
use HeyFrame\Core\Framework\Event\NestedEvent;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\System\Channel\ChannelContext;
use Symfony\Component\HttpFoundation\Request;

#[Package('framework')]
class FrontendRenderEvent extends NestedEvent implements HeyFrameChannelEvent
{
    /**
     * @var array<string, mixed>
     */
    protected array $parameters;

    /**
     * @param array<string, mixed> $parameters
     */
    public function __construct(
        protected string $view,
        array $parameters,
        protected Request $request,
        protected ChannelContext $context,
    ) {
        $this->parameters = array_merge([
            'context' => $context,
            'headerParameters' => [],
            'footerParameters' => [],
        ], $parameters);
    }

    public function getChannelContext(): ChannelContext
    {
        return $this->context;
    }

    public function setChannelContext(ChannelContext $context): void
    {
        $this->context = $context;
    }

    public function getContext(): Context
    {
        return $this->context->getContext();
    }

    public function getView(): string
    {
        return $this->view;
    }

    public function getRequest(): Request
    {
        return $this->request;
    }

    /**
     * @return array<string, mixed>
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    public function getParameter(string $key): mixed
    {
        return $this->parameters[$key] ?? null;
    }

    public function setParameter(string $key, mixed $value): void
    {
        $this->parameters[$key] = $value;
    }
}
