<?php

declare(strict_types=1);

namespace Arcanedev\LogViewer\Tests\Commands;

use Arcanedev\LogViewer\Tests\TestCase;

/**
 * Class     PublishCommandTest
 *
 * @author   ARCANEDEV <arcanedev.maroc@gmail.com>
 */
class PublishCommandTest extends TestCase
{
    /* -----------------------------------------------------------------
     |  Main Methods
     | -----------------------------------------------------------------
     */

    protected function tearDown(): void
    {
        $this->deleteConfig();
        $this->deleteLocalizations();

        parent::tearDown();
    }

    /* -----------------------------------------------------------------
     |  Tests
     | -----------------------------------------------------------------
     */

    /** @test */
    public function it_can_publish_all(): void
    {
        $this->artisan('log-viewer:publish')
             ->assertSuccessful();

        static::assertHasConfigFile();
        static::assertHasLocalizationFiles();
        // TODO: Add views assertions
    }

    /** @test */
    public function it_can_publish_all_with_force(): void
    {
        $this->artisan('log-viewer:publish', ['--force'   => true])
             ->assertSuccessful();

        static::assertHasConfigFile();
        static::assertHasLocalizationFiles();
        // TODO: Add views assertions
    }

    /** @test */
    public function it_can_publish_only_config(): void
    {
        $this->artisan('log-viewer:publish', ['--tag' => 'config'])
             ->assertSuccessful();

        static::assertHasConfigFile();
        static::assertHasNotLocalizationFiles();
        // TODO: Add views assertions
    }

    /**
     * @test
     *
     * @dataProvider  providePublishableTranslationsTags
     *
     * @param  string  $tag
     */
    public function it_can_publish_only_translations(string $tag): void
    {
        $this->artisan('log-viewer:publish', ['--tag' => $tag])
             ->assertExitCode(0);

        static::assertHasNotConfigFile();
        static::assertHasLocalizationFiles();
        // TODO: Add views assertions
    }

    public function providePublishableTranslationsTags(): array
    {
        return [
            ['translations'],
            ['log-viewer-translations'],
        ];
    }

    /* -----------------------------------------------------------------
     |  Custom Assertions
     | -----------------------------------------------------------------
     */

    /**
     * Assert config file publishes
     */
    protected function assertHasConfigFile(): void
    {
        static::assertFileExists($this->getConfigFilePath());
        static::assertTrue($this->isConfigExists());
    }

    /**
     * Assert config file publishes
     */
    protected function assertHasNotConfigFile(): void
    {
        static::assertFileDoesNotExist($this->getConfigFilePath());
        static::assertFalse($this->isConfigExists());
    }

    /**
     * Assert lang files publishes
     */
    protected function assertHasLocalizationFiles(): void
    {
        $path        = $this->getLocalizationFolder();
        $directories = $this->illuminateFile()->directories($path);
        $locales     = array_map('basename', $directories);

        static::assertEmpty(
            $missing = array_diff($locales, static::$locales),
            'The locales ['.implode(', ', $missing).'] are missing in the Arcanedev\\LogViewer\\Tests\\TestCase::$locales (line 29) for tests purposes.'
        );

        foreach ($directories as $directory) {
            static::assertFileExists($directory . '/levels.php');
        }
    }

    /**
     * Assert lang files publishes
     */
    protected function assertHasNotLocalizationFiles(): void
    {
        static::assertFalse($this->getLocalizationFolder());
    }

    /* -----------------------------------------------------------------
     |  Other Methods
     | -----------------------------------------------------------------
     */

    private function deleteConfig(): void
    {
        $config = $this->getConfigFilePath();

        if ($this->isConfigExists()) {
            $this->illuminateFile()->delete($config);
        }
    }

    /**
     * Check if LogViewer config file exists
     *
     * @return bool
     */
    private function isConfigExists(): bool
    {
        $path = $this->getConfigFilePath();

        return $this->illuminateFile()->exists($path);
    }

    /**
     * Get LogViewer config file path.
     *
     * @return string
     */
    private function getConfigFilePath(): string
    {
        return $this->getConfigPath().'/log-viewer.php';
    }

    /**
     * Get LogViewer lang folder
     */
    private function getLocalizationFolder(): string|false
    {
        return realpath(lang_path('vendor/log-viewer'));
    }

    /**
     * Delete lang folder
     */
    private function deleteLocalizations(): void
    {
        $path = $this->getLocalizationFolder();

        if ($path) {
            $this->illuminateFile()->deleteDirectory(dirname($path));
        }
    }
}
