<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\App\Source;

use HeyFrame\Core\Framework\App\AppEntity;
use HeyFrame\Core\Framework\App\Manifest\Manifest;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Util\Filesystem;
use Symfony\Component\Filesystem\Path;

/**
 * @internal
 */
#[Package('framework')]
readonly class Local implements Source
{
    public function __construct(private string $projectRoot)
    {
    }

    public static function name(): string
    {
        return 'local';
    }

    public function supports(Manifest|AppEntity $app): bool
    {
        if ($app->getSourceType() !== null && $app->getSourceType() !== self::name()) {
            return false;
        }

        return match (true) {
            $app instanceof AppEntity => $app->getSourceType() === $this->name(),
            $app instanceof Manifest => is_dir(\dirname($app->getPath())),
        };
    }

    public function filesystem(AppEntity|Manifest $app): Filesystem
    {
        return new Filesystem(
            match (true) {
                $app instanceof AppEntity => str_starts_with($app->getPath(), $this->projectRoot)
                        ? $app->getPath()
                        : Path::join($this->projectRoot, $app->getPath()),
                $app instanceof Manifest => $app->getPath(),
            }
        );
    }

    /**
     * @param array<Filesystem> $filesystems
     */
    public function reset(array $filesystems): void
    {
    }
}
