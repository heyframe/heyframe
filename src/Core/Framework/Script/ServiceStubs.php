<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Script;

use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Script\Debugging\ScriptTraces;

/**
 * This class is intended for auto-completion in twig templates. So the developer can
 * set a doc block to get auto-completion for all services.
 *
 * @example: {# @var services \HeyFrame\Core\Framework\Script\ServiceStubs #}
 *
 * @method \HeyFrame\Core\Checkout\Cart\Facade\CartFacade cart()
 * @method \HeyFrame\Core\Checkout\Cart\Facade\PriceFactory price()
 * @method \HeyFrame\Core\Framework\DataAbstractionLayer\Facade\RepositoryFacade repository()
 * @method \HeyFrame\Core\System\SystemConfig\Facade\SystemConfigFacade config()
 * @method \HeyFrame\Core\Framework\DataAbstractionLayer\Facade\ChannelRepositoryFacade store()
 * @method \HeyFrame\Core\Framework\DataAbstractionLayer\Facade\RepositoryWriterFacade writer()
 * @method \HeyFrame\Core\Framework\Routing\Facade\RequestFacade request()
 * @method \HeyFrame\Core\Framework\Script\Api\ScriptResponseFactoryFacade response()
 * @method \HeyFrame\Core\Framework\Adapter\Cache\Script\Facade\CacheInvalidatorFacade cache()
 * @method \HeyFrame\Core\Framework\Script\Api\AclFacade acl()
 */
#[Package('framework')]
final class ServiceStubs
{
    /**
     * @var array<string, array{deprecation?: string, service: object}>
     */
    private array $services = [];

    /**
     * @internal
     */
    public function __construct(private readonly string $hook)
    {
    }

    /**
     * @param array<mixed> $arguments
     *
     * @internal
     *
     * @param array<mixed> $arguments
     */
    public function __call(string $name, array $arguments): object
    {
        if (!isset($this->services[$name])) {
            throw ScriptException::serviceNotAvailableInHook($name, $this->hook);
        }

        if (isset($this->services[$name]['deprecation'])) {
            ScriptTraces::addDeprecationNotice($this->services[$name]['deprecation']);
        }

        return $this->services[$name]['service'];
    }

    /**
     * @internal
     */
    public function add(string $name, object $service, ?string $deprecationNotice = null): void
    {
        if (isset($this->services[$name])) {
            throw ScriptException::serviceAlreadyExists($name);
        }

        $this->services[$name]['service'] = $service;

        if ($deprecationNotice) {
            $this->services[$name]['deprecation'] = $deprecationNotice;
        }
    }

    /**
     * @internal
     */
    public function get(string $name): object
    {
        if (!isset($this->services[$name])) {
            throw ScriptException::serviceNotAvailableInHook($name, $this->hook);
        }

        if (isset($this->services[$name]['deprecation'])) {
            ScriptTraces::addDeprecationNotice($this->services[$name]['deprecation']);
        }

        return $this->services[$name]['service'];
    }
}
