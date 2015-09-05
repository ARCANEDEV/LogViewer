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
    /* ------------------------------------------------------------------------------------------------
     |  Main Functions
     | ------------------------------------------------------------------------------------------------
     */
    public function setUp()
    {
        parent::setUp();
    }

    public function tearDown()
    {
        $this->deleteConfig();
        $this->deleteLocalizations();

        parent::tearDown();
    }

    /* ------------------------------------------------------------------------------------------------
     |  Test Functions
     | ------------------------------------------------------------------------------------------------
     */
    /** @test */
    public function it_can_publish_all()
    {
        $this->artisan('log-viewer:publish');
        $this->assertHasConfigFile();
        $this->assertHasLocalizationFiles();
    }

    /** @test */
    public function it_can_publish_all_with_force()
    {
        $this->artisan('log-viewer:publish', [
            '--force'   => true
        ]);

        $this->assertHasConfigFile();
        $this->assertHasLocalizationFiles();
    }

    /** @test */
    public function it_can_publish_only_config()
    {
        $this->artisan('log-viewer:publish', [
            '--tag' => 'config'
        ]);

        $this->assertHasConfigFile();
        $this->assertHasNotLocalizationFiles();
    }

    /** @test */
    public function it_can_publish_only_translations()
    {
        $this->artisan('log-viewer:publish', [
            '--tag' => 'translations'
        ]);

        $this->assertHasNotConfigFile();
        $this->assertHasLocalizationFiles();
    }

    /* ------------------------------------------------------------------------------------------------
     |  Assertion Functions
     | ------------------------------------------------------------------------------------------------
     */
    /**
     * Assert config file publishes
     */
    protected function assertHasConfigFile()
    {
        $this->assertFileExists($this->getConfigFilePath());
        $this->assertTrue($this->isConfigExists());
    }

    /**
     * Assert config file publishes
     */
    protected function assertHasNotConfigFile()
    {
        $this->assertFileNotExists($this->getConfigFilePath());
        $this->assertFalse($this->isConfigExists());
    }

    /**
     * Assert lang files publishes
     */
    protected function assertHasLocalizationFiles()
    {
        $path        = $this->getLocalizationFolder();
        $directories = $this->illuminateFile()->directories($path);
        $locales     = array_map('basename', $directories);

        $this->assertEquals(self::$locales, $locales);
        foreach ($directories as $directory) {
            $this->assertFileExists($directory . '/levels.php');
        }
    }

    /**
     * Assert lang files publishes
     */
    protected function assertHasNotLocalizationFiles()
    {
        $this->assertFalse($this->getLocalizationFolder());
    }

    /* ------------------------------------------------------------------------------------------------
     |  Other Functions
     | ------------------------------------------------------------------------------------------------
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
        return $this->getConfigPath() . '/log-viewer.php';
    }

    /**
     * Get LogViewer lang folder
     *
     * @return string
     */
    private function getLocalizationFolder()
    {
        return realpath(base_path() . '/resources/lang/arcanedev/log-viewer');
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
