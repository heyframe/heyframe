<?php declare(strict_types=1);

namespace HeyFrame\Core\Content\ContentSystem\Channel\Struct;

use HeyFrame\Core\Content\ContentSystem\ContentRoute\ContentRouteEntity;
use HeyFrame\Core\Content\ContentSystem\Element\Runtime\ContentElement;
use HeyFrame\Core\Content\ContentSystem\Resolver\Struct\ResolvedData;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Struct\Struct;

#[Package('discovery')]
class ContentPageStruct extends Struct
{
    /**
     * @param array<string, mixed> $matchedParameters
     * @param array<string, mixed> $hydratedEntities
     */
    public function __construct(
        protected string $layoutId,
        protected ResolvedData $resolvedData,
        protected ?ContentRouteEntity $route,
        protected array $matchedParameters,
        protected ?ContentElement $layout = null,
        protected ?string $layoutName = null,
        protected ?string $layoutVersion = null,
        protected array $hydratedEntities = []
    ) {
    }

    public function getLayoutId(): string
    {
        return $this->layoutId;
    }

    public function getResolvedData(): ResolvedData
    {
        return $this->resolvedData;
    }

    public function getRoute(): ?ContentRouteEntity
    {
        return $this->route;
    }

    public function setRoute(ContentRouteEntity $route): void
    {
        $this->route = $route;
    }

    /**
     * @return array<string, mixed>
     */
    public function getMatchedParameters(): array
    {
        return $this->matchedParameters;
    }

    public function getLayout(): ?ContentElement
    {
        return $this->layout;
    }

    public function setLayout(ContentElement $layout): void
    {
        $this->layout = $layout;
    }

    public function getLayoutName(): ?string
    {
        return $this->layoutName;
    }

    public function setLayoutName(string $layoutName): void
    {
        $this->layoutName = $layoutName;
    }

    public function getLayoutVersion(): ?string
    {
        return $this->layoutVersion;
    }

    public function setLayoutVersion(string $layoutVersion): void
    {
        $this->layoutVersion = $layoutVersion;
    }

    /**
     * @return array<string, mixed>
     */
    public function getHydratedEntities(): array
    {
        return $this->hydratedEntities;
    }

    /**
     * @param array<string, mixed> $hydratedEntities
     */
    public function setHydratedEntities(array $hydratedEntities): void
    {
        $this->hydratedEntities = $hydratedEntities;
    }

    public function getApiAlias(): string
    {
        return 'content_page';
    }
}
