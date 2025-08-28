<?php declare(strict_types=1);

namespace HeyFrame\Core\Framework\Update\Services;

use HeyFrame\Core\DevOps\Environment\EnvironmentHelper;
use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Update\Struct\Version;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * @phpstan-import-type VersionFixedVulnerabilities from Version
 */
/**
 * @phpstan-import-type VersionFixedVulnerabilities from Version
 */
#[Package('framework')]
class ApiClient
{
    /**
     * @internal
     */
    public function __construct(
        private readonly HttpClientInterface $client,
        private readonly bool $heyframeUpdateEnabled,
        private readonly string $heyframeVersion,
        private readonly string $projectDir
    ) {
    }

    public function checkForUpdates(): Version
    {
        $fakeVersion = EnvironmentHelper::getVariable('SW_RECOVERY_NEXT_VERSION');
        if (\is_string($fakeVersion)) {
            return new Version([
                'version' => $fakeVersion,
                'title' => 'HeyFrame ' . $fakeVersion,
                'body' => 'This is a fake version for testing purposes',
                'date' => new \DateTimeImmutable(),
                'fixedVulnerabilities' => [],
            ]);
        }

        if (!$this->heyframeUpdateEnabled) {
            return new Version();
        }

        try {
            /** @var array{title: string, body: string, date: string, version: string, fixedVulnerabilities: VersionFixedVulnerabilities[]} $github */
            $github = $this->client->request('GET', 'https://releases.heyframe.net/changelog/' . $this->determineLatestHeyFrameVersion() . '.json')->toArray();
        } catch (ClientException $e) {
            if ($e->getCode() === Response::HTTP_NOT_FOUND || $e->getCode() === Response::HTTP_FORBIDDEN) {
                return new Version();
            }

            throw $e;
        }

        $version = new Version();
        $version->title = $github['title'];
        $version->body = $github['body'];
        $version->date = new \DateTimeImmutable($github['date']);
        $version->version = $github['version'];
        $version->fixedVulnerabilities = $github['fixedVulnerabilities'];

        return $version;
    }

    public function downloadRecoveryTool(): void
    {
        if (\is_string(EnvironmentHelper::getVariable('SW_RECOVERY_NEXT_VERSION'))) {
            return;
        }

        $content = $this->client->request('GET', 'https://github.com/heyframe/web-installer/releases/latest/download/heyframe-installer.phar.php')->getContent();

        file_put_contents($this->projectDir . '/public/heyframe-installer.phar.php', $content);
    }

    private function determineLatestHeyFrameVersion(): string
    {
        /** @var non-empty-array<string> $versions */
        $versions = $this->client->request('GET', 'https://releases.heyframe.net/changelog/index.json')->toArray();

        usort($versions, function ($a, $b) {
            return version_compare($b, $a);
        });

        // Index them by major version
        $mappedVersions = [];

        foreach ($versions as $version) {
            if (str_contains($version, 'rc')) {
                continue;
            }

            $major = substr($version, 0, 3);

            if (isset($mappedVersions[$major])) {
                continue;
            }

            $mappedVersions[$major] = $version;
        }

        $currentMajor = substr($this->heyframeVersion, 0, 3);
        if (!isset($mappedVersions[$currentMajor])) {
            return strtolower($this->heyframeVersion);
        }

        $latestVersion = $mappedVersions[$currentMajor];

        $first = (int) substr($this->heyframeVersion, 0, 1);
        $second = (int) substr($this->heyframeVersion, 2, 1);
        ++$second;

        if (isset($mappedVersions[$first . '.' . $second])) {
            $latestVersion = $mappedVersions[$first . '.' . $second];
        }

        return $latestVersion;
    }
}
