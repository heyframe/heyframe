<?php declare(strict_types=1);

namespace HeyFrame\Tests\Unit\Core\System\Snippet\Command;

use HeyFrame\Core\Framework\Log\Package;
use HeyFrame\Core\Framework\Plugin\PluginCollection;
use HeyFrame\Core\System\Snippet\Command\LintTranslationFilesCommand;
use HeyFrame\Core\System\Snippet\Command\Util\CountryAgnosticFileLinter;
use HeyFrame\Core\Test\Stub\DataAbstractionLayer\StaticEntityRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Filesystem\Filesystem;

/**
 * @internal
 *
 * @phpstan-type ExpectedDataProviderType array{
 *     admin: array<string, string>,
 *     frontend: array<string, string>,
 *     adminInSubdir: array<string, string>,
 *     frontendInSubdir: array<string, string>,
 * }
 * @phpstan-type PromptType array<string, array{
 *     dir: string,
 *     prefix: string,
 *     isAdmin: bool,
 *     options: list<string>,
 * }>
 * @phpstan-type FaultyFixturesDataProviderType array{
 *     config: array{params: array<string, string>, promptInput: PromptType},
 *     counts: array{frontend: int, administration: int, faulty: int, fixed: int},
 *     expectedValid: ExpectedDataProviderType,
 *     expectedFaulty: ExpectedDataProviderType,
 *     expectedFixed: ExpectedDataProviderType,
 *     expectedNotFixed: ExpectedDataProviderType,
 *  }
 */
#[Package('discovery')]
#[Group('slow')]
#[CoversClass(LintTranslationFilesCommand::class)]
class LintTranslationFilesCommandTest extends TestCase
{
    private const FIXTURES_SOURCE_PATH = 'tests/unit/Core/System/Snippet/Command/_fixtures';
    private const FIXTURES_PATH = self::FIXTURES_SOURCE_PATH . '/../temp';
    private const FIXTURES_SUBDIRECTORY = 'subdir';

    private CommandTester $tester;

    protected function setUp(): void
    {
        $filesystem = new Filesystem();
        $filesystem->mirror(self::FIXTURES_SOURCE_PATH, self::FIXTURES_PATH);

        /** @var StaticEntityRepository<PluginCollection> $pluginRepository */
        $pluginRepository = new StaticEntityRepository([]);

        $this->tester = new CommandTester(new LintTranslationFilesCommand(
            new CountryAgnosticFileLinter(
                new Filesystem(),
                $pluginRepository,
            ),
        ));
    }

    protected function tearDown(): void
    {
        (new Filesystem())->remove(self::FIXTURES_PATH);
    }

    public function testLanguageFilesHaveACountryAgnosticCounterpart(): void
    {
        $this->tester->execute([]);

        $this->tester->assertCommandIsSuccessful($this->tester->getDisplay());
        static::assertStringContainsString(
            '[OK] All translation files are named correctly.',
            $this->getDisplayOutput(),
            'Failed asserting that all translation files are named correctly. Please run `php bin/console translation:lint-filenames --fix` to fix the issues.'
        );
    }

    public function testLanguageFilesHaveACountryAgnosticCounterpartWithFix(): void
    {
        $this->tester->execute(['--fix' => true]);

        $this->tester->assertCommandIsSuccessful($this->tester->getDisplay());
        static::assertStringContainsString(
            '[OK] All translation files are named correctly. Nothing to fix.',
            $this->getDisplayOutput(),
            'Failed asserting that all translation files are named correctly. Please run `php bin/console translation:lint-filenames --fix` to fix the issues.'
        );
    }

    /**
     * @return \Generator<string, FaultyFixturesDataProviderType>
     */
    public static function faultyFixturesDataProvider(): \Generator
    {
        $adminValid = ['be-BE' => 'be'];
        $frontendValid = ['de-DE' => 'de'];
        $adminFaulty = ['jp-JP' => 'jp', 'nl-BE' => 'nl', 'nl-NL' => 'nl'];
        $frontendFaulty = ['fr-BE' => 'fr', 'fr-FR' => 'fr', 'it-IT' => 'it'];

        $adminInSubdirValid = ['hr-HR' => 'hr'];
        $frontendInSubdirValid = ['en-GB' => 'en', 'en-US' => 'en'];
        $adminInSubdirFaulty = ['ko-KR' => 'ko'];
        $frontendInSubdirFaulty = ['es-ES' => 'es', 'es-AR' => 'es'];

        $adminFixed = ['jp-JP' => 'jp', 'nl-NL' => 'nl'];
        $frontendFixed = ['fr-FR' => 'fr', 'it-IT' => 'it'];
        $adminInSubdirFixed = ['ko-KR' => 'ko'];
        $frontendInSubdirFixed = ['es-AR' => 'es'];

        $adminNotFixed = ['nl-BE' => 'nl'];
        $frontendNotFixed = ['fr-BE' => 'fr'];
        $adminInSubdirNotFixed = [];
        $frontendInSubdirNotFixed = ['es-ES' => 'es'];

        $promptInputWithoutSubdir = [
            'nl-NL' => [
                'dir' => '',
                'prefix' => 'nl',
                'isAdmin' => true,
                'options' => ['nl-BE', 'nl-NL'],
            ],
            'fr-FR' => [
                'dir' => '',
                'prefix' => 'fr',
                'isAdmin' => false,
                'options' => ['fr-BE', 'fr-FR'],
            ],
        ];
        $promptInput = [
            ...$promptInputWithoutSubdir,
            'es-AR' => [
                'dir' => '/' . self::FIXTURES_SUBDIRECTORY,
                'prefix' => 'es',
                'isAdmin' => false,
                'options' => ['es-AR', 'es-ES'],
            ],
        ];

        yield 'All Fixtures' => [
            'config' => [
                'params' => [],
                'promptInput' => $promptInput,
            ],
            'counts' => [
                'frontend' => 10,
                'administration' => 8,
                'faulty' => 9,
                'fixed' => 6,
            ],
            'expectedValid' => [
                'admin' => $adminValid,
                'frontend' => $frontendValid,
                'adminInSubdir' => $adminInSubdirValid,
                'frontendInSubdir' => $frontendInSubdirValid,
            ],
            'expectedFaulty' => [
                'admin' => $adminFaulty,
                'frontend' => $frontendFaulty,
                'adminInSubdir' => $adminInSubdirFaulty,
                'frontendInSubdir' => $frontendInSubdirFaulty,
            ],
            'expectedFixed' => [
                'admin' => $adminFixed,
                'frontend' => $frontendFixed,
                'adminInSubdir' => $adminInSubdirFixed,
                'frontendInSubdir' => $frontendInSubdirFixed,
            ],
            'expectedNotFixed' => [
                'admin' => $adminNotFixed,
                'frontend' => $frontendNotFixed,
                'adminInSubdir' => $adminInSubdirNotFixed,
                'frontendInSubdir' => $frontendInSubdirNotFixed,
            ],
        ];

        yield 'Ignore Subfolder' => [
            'config' => [
                'params' => ['--ignore' => self::FIXTURES_SUBDIRECTORY],
                'promptInput' => $promptInputWithoutSubdir,
            ],
            'counts' => [
                'frontend' => 5,
                'administration' => 5,
                'faulty' => 6,
                'fixed' => 4,
            ],
            'expectedValid' => [
                'admin' => $adminValid,
                'frontend' => $frontendValid,
                'adminInSubdir' => [...$adminInSubdirFaulty, ...$adminInSubdirValid],
                'frontendInSubdir' => [...$frontendInSubdirFaulty, ...$frontendInSubdirValid],
            ],
            'expectedFaulty' => [
                'admin' => $adminFaulty,
                'frontend' => $frontendFaulty,
                'adminInSubdir' => [],
                'frontendInSubdir' => [],
            ],
            'expectedFixed' => [
                'admin' => $adminFixed,
                'frontend' => $frontendFixed,
                'adminInSubdir' => [],
                'frontendInSubdir' => [],
            ],
            'expectedNotFixed' => [
                'admin' => $adminNotFixed,
                'frontend' => $frontendNotFixed,
                'adminInSubdir' => [...$adminInSubdirFixed, ...$adminInSubdirNotFixed],
                'frontendInSubdir' => [...$frontendInSubdirFixed, ...$frontendInSubdirNotFixed],
            ],
        ];
    }

    /**
     * @param array{params: array<string, string|true>, promptInput: PromptType} $config
     * @param array{frontend: int, administration: int, faulty: int, fixed: int} $counts
     * @param ExpectedDataProviderType $expectedValid
     * @param ExpectedDataProviderType $expectedFaulty
     * @param ExpectedDataProviderType $expectedFixed
     * @param ExpectedDataProviderType $expectedNotFixed
     */
    #[DataProvider('faultyFixturesDataProvider')]
    public function testFaultyFixtureFiles(array $config, array $counts, array $expectedValid, array $expectedFaulty, array $expectedFixed, array $expectedNotFixed): void
    {
        $this->tester->execute(['--dir' => self::FIXTURES_PATH, ...$config['params']]);

        static::assertSame(Command::FAILURE, $this->tester->getStatusCode());

        $this->assertFileCount('Storefront', $counts['frontend']);
        $this->assertFileCount('Administration', $counts['administration']);
        $this->assertErrorCount($counts['faulty']);

        $this->assertHaveFaultyFilenames($expectedFaulty['frontend'], false);
        $this->assertHaveFaultyFilenames($expectedFaulty['frontendInSubdir'], false, true);
        $this->assertHaveFaultyFilenames($expectedFaulty['admin'], true);
        $this->assertHaveFaultyFilenames($expectedFaulty['adminInSubdir'], true, true);

        $this->assertNotHaveFaultyFilenames($expectedValid['frontend'], false);
        $this->assertNotHaveFaultyFilenames($expectedValid['frontendInSubdir'], false, true);
        $this->assertNotHaveFaultyFilenames($expectedValid['admin'], true);
        $this->assertNotHaveFaultyFilenames($expectedValid['adminInSubdir'], true, true);

        static::assertStringContainsString(
            '[ERROR] Every country-specific translation file must have a corresponding agnostic file.',
            $this->getDisplayOutput(),
        );
    }

    /**
     * @param array{params: array<string, string|true>, promptInput: PromptType} $config
     * @param array{frontend: int, administration: int, faulty: int, fixed: int} $counts
     * @param ExpectedDataProviderType $expectedValid
     * @param ExpectedDataProviderType $expectedFaulty
     * @param ExpectedDataProviderType $expectedFixed
     * @param ExpectedDataProviderType $expectedNotFixed
     */
    #[DataProvider('faultyFixturesDataProvider')]
    public function testFaultyFixtureFilesWithFix(array $config, array $counts, array $expectedValid, array $expectedFaulty, array $expectedFixed, array $expectedNotFixed): void
    {
        $this->tester->setInputs(\array_keys($config['promptInput']));
        $this->tester->execute([
            '--dir' => self::FIXTURES_PATH,
            '--fix' => true,
            ...$config['params'],
        ]);

        $this->tester->assertCommandIsSuccessful($this->tester->getDisplay());

        $this->assertErrorCount($counts['faulty']);
        $this->assertFixedFileCount($counts['fixed']);

        $this->assertHaveFaultyFilenames($expectedFaulty['frontend'], false);
        $this->assertHaveFaultyFilenames($expectedFaulty['frontendInSubdir'], false, true);
        $this->assertHaveFaultyFilenames($expectedFaulty['admin'], true);
        $this->assertHaveFaultyFilenames($expectedFaulty['adminInSubdir'], true, true);

        $this->assertNotHaveFaultyFilenames($expectedValid['frontend'], false);
        $this->assertNotHaveFaultyFilenames($expectedValid['frontendInSubdir'], false, true);
        $this->assertNotHaveFaultyFilenames($expectedValid['admin'], true);
        $this->assertNotHaveFaultyFilenames($expectedValid['adminInSubdir'], true, true);

        $this->assertHaveBeenPrompted($config['promptInput']);

        $this->assertHaveBeenFixed($expectedFixed['frontend'], false);
        $this->assertHaveBeenFixed($expectedFixed['admin'], true);
        $this->assertHaveBeenFixed($expectedFixed['frontendInSubdir'], false, true);
        $this->assertHaveBeenFixed($expectedFixed['adminInSubdir'], true, true);

        $this->assertHaveNotBeenFixed($expectedNotFixed['frontend'], false);
        $this->assertHaveNotBeenFixed($expectedNotFixed['admin'], true);
        $this->assertHaveNotBeenFixed($expectedNotFixed['frontendInSubdir'], false, true);
        $this->assertHaveNotBeenFixed($expectedNotFixed['adminInSubdir'], true, true);

        static::assertStringContainsString(
            '[OK] All faulty files have been fixed.',
            $this->getDisplayOutput(),
        );
    }

    private function assertFileCount(string $domain, int $expectedFileCount): void
    {
        static::assertStringContainsString(\sprintf(
            '%s files found: %s',
            $domain,
            $expectedFileCount,
        ), $this->getDisplayOutput());
    }

    private function assertErrorCount(int $expectedErrorCount): void
    {
        static::assertStringContainsString('Issues found: ' . $expectedErrorCount, $this->getDisplayOutput());
    }

    private function assertFixedFileCount(int $expectedFileCount): void
    {
        static::assertStringContainsString('Files fixed: ' . $expectedFileCount, $this->getDisplayOutput());
    }

    /**
     * @param array<string, string> $expected
     */
    private function assertHaveFaultyFilenames(array $expected, bool $isAdmin, bool $inSubDirectory = false): void
    {
        $filePath = self::FIXTURES_PATH . ($inSubDirectory ? '/' . self::FIXTURES_SUBDIRECTORY : '');
        $domainPrefix = $isAdmin ? '' : 'frontend.';

        foreach ($expected as $expectedLocale => $expectedLanguagePrefix) {
            $expectedString = \sprintf(
                '%s%s.json │ %s │ %s │ %s%s.json',
                $domainPrefix,
                $expectedLocale,
                $filePath,
                $expectedLocale,
                $domainPrefix,
                $expectedLanguagePrefix,
            );
            static::assertStringContainsString($expectedString, $this->getDisplayOutput());
        }
    }

    /**
     * @param array<string, string> $expected
     */
    private function assertNotHaveFaultyFilenames(array $expected, bool $isAdmin, bool $inSubDirectory = false): void
    {
        $filePath = self::FIXTURES_PATH . ($inSubDirectory ? '/' . self::FIXTURES_SUBDIRECTORY : '');
        $domainPrefix = $isAdmin ? '' : 'frontend.';

        foreach ($expected as $expectedLocale => $expectedLanguagePrefix) {
            $notExpectedString = \sprintf(
                '%s%s.json │ %s │ %s │ %s%s.json',
                $domainPrefix,
                $expectedLocale,
                $filePath,
                $expectedLocale,
                $domainPrefix,
                $expectedLanguagePrefix,
            );
            static::assertStringNotContainsString($notExpectedString, $this->getDisplayOutput());
        }
    }

    /**
     * @param PromptType $expectedPrompts
     */
    private function assertHaveBeenPrompted(array $expectedPrompts): void
    {
        foreach ($expectedPrompts as $expectedData) {
            $newFileDirectory = self::FIXTURES_PATH . $expectedData['dir'];
            $domainPrefix = $expectedData['isAdmin'] ? '' : 'frontend.';
            $expectedPrompt = \sprintf(
                'Found multiple country-specific candidates for "%s/%s%s.json". Select the file to rename:',
                $newFileDirectory,
                $domainPrefix,
                $expectedData['prefix'],
            );
            static::assertStringContainsString($expectedPrompt, $this->getDisplayOutput());

            foreach ($expectedData['options'] as $locale) {
                static::assertStringContainsString(
                    \sprintf('[%s] %s/%s%s.json', $locale, $newFileDirectory, $domainPrefix, $locale),
                    $this->getDisplayOutput(),
                );
            }
        }
    }

    /**
     * @param array<string, string> $expected
     */
    private function assertHaveBeenFixed(array $expected, bool $isAdmin, bool $inSubDirectory = false): void
    {
        $newFileDirectory = self::FIXTURES_PATH . ($inSubDirectory ? '/' . self::FIXTURES_SUBDIRECTORY : '');
        $domainPrefix = $isAdmin ? '' : 'frontend.';

        foreach ($expected as $originalLocale => $expectedTargetLocale) {
            $expectedString = \sprintf(
                '%s%s.json │ %s%s.json │ %s',
                $domainPrefix,
                $originalLocale,
                $domainPrefix,
                $expectedTargetLocale,
                $newFileDirectory,
            );

            static::assertStringContainsString($expectedString, $this->getDisplayOutput());
            static::assertFileExists(\sprintf('%s/%s%s.json', $newFileDirectory, $domainPrefix, $expectedTargetLocale));
            static::assertFileDoesNotExist(\sprintf('%s/%s%s.json', $newFileDirectory, $domainPrefix, $originalLocale));
        }
    }

    /**
     * @param array<string, string> $expected
     */
    private function assertHaveNotBeenFixed(array $expected, bool $isAdmin, bool $inSubDirectory = false): void
    {
        $newFileDirectory = self::FIXTURES_PATH . ($inSubDirectory ? '/' . self::FIXTURES_SUBDIRECTORY : '');
        $domainPrefix = $isAdmin ? '' : 'frontend.';

        foreach ($expected as $originalLocale => $expectedTargetLocale) {
            $notExpectedString = \sprintf(
                '%s%s.json │ %s%s.json │ %s',
                $domainPrefix,
                $originalLocale,
                $domainPrefix,
                $expectedTargetLocale,
                $newFileDirectory,
            );
            static::assertStringNotContainsString($notExpectedString, $this->getDisplayOutput());
            static::assertFileExists(\sprintf('%s/%s%s.json', $newFileDirectory, $domainPrefix, $originalLocale));
        }
    }

    private function getDisplayOutput(): string
    {
        $output = preg_replace('/\s+/', ' ', $this->tester->getDisplay(true));
        static::assertIsString($output);

        return $output;
    }
}
