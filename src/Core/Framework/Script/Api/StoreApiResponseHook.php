<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Script\Api;

use HeyFrame\Core\Framework\DataAbstractionLayer\Facade\ChannelRepositoryFacadeHookFactory;
use HeyFrame\Core\Framework\DataAbstractionLayer\Facade\RepositoryFacadeHookFactory;
use HeyFrame\Core\Framework\DataAbstractionLayer\Facade\RepositoryWriterFacadeHookFactory;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Routing\Facade\RequestFacadeFactory;
use HeyFrame\Core\Framework\Script\Execution\Awareness\ChannelContextAware;
use HeyFrame\Core\Framework\Script\Execution\Awareness\ScriptResponseAwareTrait;
use HeyFrame\Core\Framework\Script\Execution\Awareness\StoppableHook;
use HeyFrame\Core\Framework\Script\Execution\Awareness\StoppableHookTrait;
use HeyFrame\Core\Framework\Script\Execution\FunctionHook;
use HeyFrame\Core\System\Channel\ChannelContext;
use HeyFrame\Core\System\SystemConfig\Facade\SystemConfigFacadeHookFactory;

/**
 * Triggered when the api endpoint /front-api/script/{hook} is called. Used to provide the HTTP-Response.
 * This function is only called when no response for the provided cache key is cached, or no `cache_key` function implemented.
 *
 * @hook-use-case custom_endpoint
 *
 * @since 6.4.9.0
 *
 * @final
 */
#[Package('framework')]
class StoreApiResponseHook extends FunctionHook implements ChannelContextAware, StoppableHook
{
    use ScriptResponseAwareTrait;
    use StoppableHookTrait;

    final public const FUNCTION_NAME = 'response';

    /**
     * @param array<mixed> $request
     * @param array<mixed> $query
     */
    public function __construct(
        private readonly string $name,
        private readonly array $request,
        private readonly array $query,
        private readonly ChannelContext $channelContext
    ) {
        parent::__construct($channelContext->getContext());
    }

    /**
     * @return array<mixed>
     */
    public function getRequest(): array
    {
        return $this->request;
    }

    /**
     * @return array<mixed>
     */
    public function getQuery(): array
    {
        return $this->query;
    }

    public function getChannelContext(): ChannelContext
    {
        return $this->channelContext;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getFunctionName(): string
    {
        return self::FUNCTION_NAME;
    }

    public static function getServiceIds(): array
    {
        return [
            RepositoryFacadeHookFactory::class,
            SystemConfigFacadeHookFactory::class,
            ChannelRepositoryFacadeHookFactory::class,
            RepositoryWriterFacadeHookFactory::class,
            ScriptResponseFactoryFacadeHookFactory::class,
            RequestFacadeFactory::class,
        ];
    }
}
