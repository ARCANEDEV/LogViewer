<?php namespace Arcanedev\LogViewer\Tests\Utilities;

use Arcanedev\LogViewer\Tests\TestCase;
use Arcanedev\LogViewer\Utilities\Filesystem;

/**
 * Class     FilesystemTest
 *
 * @package  Arcanedev\LogViewer\Tests\Utilities
 * @author   ARCANEDEV <arcanedev.maroc@gmail.com>
 */
class FilesystemTest extends TestCase
{
    /* ------------------------------------------------------------------------------------------------
     |  Properties
     | ------------------------------------------------------------------------------------------------
     */
    /** @var Filesystem */
    private $filesystem;

    /* ------------------------------------------------------------------------------------------------
     |  Main Functions
     | ------------------------------------------------------------------------------------------------
     */
    public function setUp()
    {
        parent::setUp();

        $this->filesystem = $this->filesystem();
    }

    public function tearDown()
    {
        unset($this->filesystem);

        parent::tearDown();
    }

    /* ------------------------------------------------------------------------------------------------
     |  Test Functions
     | ------------------------------------------------------------------------------------------------
     */
    /** @test */
    public function it_can_be_instantiated()
    {
        $this->assertInstanceOf(Filesystem::class, $this->filesystem);
    }

    /** @test */
    public function it_can_get_filesystem_instance()
    {
        $this->assertInstanceOf(
            \Illuminate\Filesystem\Filesystem::class,
            $this->filesystem->getInstance()
        );
    }

    /** @test */
    public function it_can_get_all_valid_log_files()
    {
        $files = $this->filesystem->logs();

        $this->assertCount(2, $files);
    }

    /** @test */
    public function it_can_get_all_custom_log_files()
    {
        $files = $this->filesystem
            ->setPrefixPattern('laravel-cli-')
            ->logs();

        $this->assertCount(1, $files);
    }

    /** @test */
    public function it_can_get_all_log_files()
    {
        $files = $this->filesystem->all();
        $this->assertCount(5, $files);

        foreach ($files as $file) {
            $this->assertStringEndsWith('.log', $file);
        }
    }

    /** @test */
    public function it_can_read_file()
    {
        $date = '2015-01-01';

        $file = $this->filesystem->read($date);

        $this->assertNotEmpty($file);
        $this->assertStringStartsWith('[' . $date, $file);
    }

    /** @test */
    public function it_can_delete_file()
    {
        $date = date('Y-m-d');

        $this->createDummyLog($date);

        // Assert log exists
        $file = $this->filesystem->read($date);

        $this->assertNotEmpty($file);

        // Assert log deletion
        try {
            $deleted = $this->filesystem->delete($date);
            $message = '';
        }
        catch (\Exception $e) {
            $deleted = false;
            $message = $e->getMessage();
        }

        $this->assertTrue($deleted, $message);
    }

    /** @test */
    public function it_can_get_files()
    {
        $files = $this->filesystem->logs();

        $this->assertCount(2, $files);
        foreach ($files as $file) {
            $this->assertFileExists($file);
        }
    }

    /** @test */
    public function it_can_set_a_custom_path()
    {
        $this->filesystem->setPath(storage_path('custom-path-logs'));

        $files = $this->filesystem->logs();

        $this->assertCount(1, $files);
        foreach ($files as $file) {
            $this->assertFileExists($file);
        }
    }


    /** @test */
    public function it_can_get_file_path_by_date()
    {
        $this->assertFileExists(
            $this->filesystem->path('2015-01-01')
        );
    }

    /** @test */
    public function it_can_get_dates_from_log_files()
    {
        $dates = $this->filesystem->dates();

        $this->assertDates($dates);
    }

    /** @test */
    public function it_can_get_dates_with_paths_from_log_files()
    {
        $dates = $this->filesystem->dates(true);

        foreach ($dates as $date => $path) {
            $this->assertDate($date);
            $this->assertFileExists($path);
        }
    }

    /**
     * @test
     *
     * @expectedException \Arcanedev\LogViewer\Exceptions\FilesystemException
     */
    public function it_must_throw_a_filesystem_exception_on_read()
    {
        $this->filesystem->read('2222-11-11'); // Future FTW
    }

    /**
     * @test
     *
     * @expectedException \Arcanedev\LogViewer\Exceptions\FilesystemException
     */
    public function it_must_throw_a_filesystem_exception_on_delete()
    {
        $this->filesystem->delete('2222-11-11'); // Future FTW
    }

    /** @test */
    public function it_can_set_and_get_pattern()
    {
        $this->assertEquals(
            'laravel-[0-9][0-9][0-9][0-9]-[0-9][0-9]-[0-9][0-9].log',
            $this->filesystem->getPattern()
        );

        $this->filesystem->setExtension('');

        $this->assertEquals(
            'laravel-[0-9][0-9][0-9][0-9]-[0-9][0-9]-[0-9][0-9]',
            $this->filesystem->getPattern()
        );

        $this->filesystem->setPrefixPattern('laravel-cli-');

        $this->assertEquals(
            'laravel-cli-[0-9][0-9][0-9][0-9]-[0-9][0-9]-[0-9][0-9]',
            $this->filesystem->getPattern()
        );

        $this->filesystem->setDatePattern('[0-9][0-9][0-9][0-9]');

        $this->assertEquals(
            'laravel-cli-[0-9][0-9][0-9][0-9]',
            $this->filesystem->getPattern()
        );

        $this->filesystem->setPattern();

        $this->assertEquals(
            'laravel-[0-9][0-9][0-9][0-9]-[0-9][0-9]-[0-9][0-9].log',
            $this->filesystem->getPattern()
        );

        $this->filesystem->setPattern(
            'laravel-', '[0-9][0-9][0-9][0-9]-[0-9][0-9]-[0-9][0-9]', '.log'
        );

        $this->assertEquals(
            'laravel-[0-9][0-9][0-9][0-9]-[0-9][0-9]-[0-9][0-9].log',
            $this->filesystem->getPattern()
        );
    }
}
