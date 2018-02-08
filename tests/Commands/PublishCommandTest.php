<?php namespace Arcanedev\LogViewer\Tests\Commands;

use Arcanedev\LogViewer\Tests\TestCase;

/**
 * Class     PublishCommandTest
 *
 * @package  Arcanedev\LogViewer\Tests\Commands
 * @author   ARCANEDEV <arcanedev.maroc@gmail.com>
 */
class PublishCommandTest extends TestCase
{
    /* -----------------------------------------------------------------
     |  Main Methods
     | -----------------------------------------------------------------
     */

    protected function setUp()
    {
        parent::setUp();

        //
    }

    protected function tearDown()
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
    public function it_can_publish_all()
    {
        $code = $this->artisan('log-viewer:publish');

        static::assertSame(0, $code);
        static::assertHasConfigFile();
        static::assertHasLocalizationFiles();
        // TODO: Add views assertions
    }

    /** @test */
    public function it_can_publish_all_with_force()
    {
        $code = $this->artisan('log-viewer:publish', [
            '--force'   => true
        ]);

        static::assertEquals(0, $code);
        static::assertHasConfigFile();
        static::assertHasLocalizationFiles();
        // TODO: Add views assertions
    }

    /** @test */
    public function it_can_publish_only_config()
    {
        $code = $this->artisan('log-viewer:publish', [
            '--tag' => 'config'
        ]);

        static::assertSame(0, $code);
        static::assertHasConfigFile();
        static::assertHasNotLocalizationFiles();
        // TODO: Add views assertions
    }

    /** @test */
    public function it_can_publish_only_translations()
    {
        $code = $this->artisan('log-viewer:publish', [
            '--tag' => 'lang'
        ]);

        static::assertSame(0, $code);
        static::assertHasNotConfigFile();
        static::assertHasLocalizationFiles();
        // TODO: Add views assertions
    }

    /* -----------------------------------------------------------------
     |  Custom Assertions
     | -----------------------------------------------------------------
     */

    /**
     * Assert config file publishes
     */
    protected function assertHasConfigFile()
    {
        static::assertFileExists($this->getConfigFilePath());
        static::assertTrue($this->isConfigExists());
    }

    /**
     * Assert config file publishes
     */
    protected function assertHasNotConfigFile()
    {
        static::assertFileNotExists($this->getConfigFilePath());
        static::assertFalse($this->isConfigExists());
    }

    /**
     * Assert lang files publishes
     */
    protected function assertHasLocalizationFiles()
    {
        $path        = $this->getLocalizationFolder();
        $directories = $this->illuminateFile()->directories($path);
        $locales     = array_map('basename', $directories);

        static::assertEmpty(
            $missing = array_diff($locales, self::$locales),
            'The locales ['.implode(', ', $missing).'] are missing in the Arcanedev\\LogViewer\\Tests\\TestCase::$locales (line 29) for tests purposes.'
        );

        foreach ($directories as $directory) {
            static::assertFileExists($directory . '/levels.php');
        }
    }

    /**
     * Assert lang files publishes
     */
    protected function assertHasNotLocalizationFiles()
    {
        static::assertFalse($this->getLocalizationFolder());
    }

    /* -----------------------------------------------------------------
     |  Other Methods
     | -----------------------------------------------------------------
     */

    private function deleteConfig()
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
    private function isConfigExists()
    {
        $path = $this->getConfigFilePath();

        return $this->illuminateFile()->exists($path);
    }

    /**
     * Get LogViewer config file path.
     *
     * @return string
     */
    private function getConfigFilePath()
    {
        return $this->getConfigPath().'/log-viewer.php';
    }

    /**
     * Get LogViewer lang folder
     *
     * @return string
     */
    private function getLocalizationFolder()
    {
        return realpath(base_path().'/resources/lang/vendor/log-viewer');
    }

    /**
     * Delete lang folder
     */
    private function deleteLocalizations()
    {
        $path = $this->getLocalizationFolder();

        if ($path) {
            $this->illuminateFile()->deleteDirectory(dirname($path));
        }
    }
}
