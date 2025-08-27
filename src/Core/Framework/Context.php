<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework;

use HeyFrame\Core\Defaults;
use HeyFrame\Core\Framework\Api\Context\ContextSource;
use HeyFrame\Core\Framework\Struct\Struct;
use Symfony\Component\Serializer\Attribute\Ignore;

class Context extends Struct
{
    final public const SYSTEM_SCOPE = 'system';
    final public const USER_SCOPE = 'user';
    final public const CRUD_API_SCOPE = 'crud';
    final public const SKIP_TRIGGER_FLOW = 'skipTriggerFlow';

    protected string $scope = self::USER_SCOPE;

    protected bool $rulesLocked = false;

    #[Ignore]
    protected array $extensions = [];

    /**
     * @param array<string> $ruleIds
     * @param non-empty-list<string> $languageIdChain
     */
    public function __construct(
        protected ContextSource $source,
        protected array $ruleIds = [],
        protected array $languageIdChain = [Defaults::LANGUAGE_SYSTEM],
        protected string $versionId = Defaults::LIVE_VERSION,
    ) {
    }

    public function getVersionId(): string
    {
        return $this->versionId;
    }

    public function getLanguageId(): string
    {
        return $this->languageIdChain[0];
    }

    public function getSource(): ContextSource
    {
        return $this->source;
    }
}
