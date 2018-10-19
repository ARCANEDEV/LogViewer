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
    /* -----------------------------------------------------------------
     |  Properties
     | -----------------------------------------------------------------
     */

    /** @var  \Arcanedev\LogViewer\Utilities\Filesystem */
    private $filesystem;

    /* -----------------------------------------------------------------
     |  Main Methods
     | -----------------------------------------------------------------
     */

    protected function setUp()
    {
        parent::setUp();

        $this->filesystem = $this->filesystem();
    }

    protected function tearDown()
    {
        unset($this->filesystem);

        parent::tearDown();
    }

    /* -----------------------------------------------------------------
     |  Tests
     | -----------------------------------------------------------------
     */

    /** @test */
    public function it_can_be_instantiated()
    {
        static::assertInstanceOf(Filesystem::class, $this->filesystem);
    }

    /** @test */
    public function it_can_get_filesystem_instance()
    {
        static::assertInstanceOf(
            \Illuminate\Filesystem\Filesystem::class,
            $this->filesystem->getInstance()
        );
    }

    /** @test */
    public function it_can_get_all_valid_log_files()
    {
        static::assertCount(2, $this->filesystem->logs());
    }

    /** @test */
    public function it_can_get_all_custom_log_files()
    {
        $files = $this->filesystem
            ->setPrefixPattern('laravel-cli-')
            ->logs();

        static::assertCount(1, $files);
    }

    /** @test */
    public function it_can_get_all_log_files()
    {
        $files = $this->filesystem->all();

        static::assertCount(5, $files);

        foreach ($files as $file) {
            static::assertStringEndsWith('.log', $file);
        }
    }

    /** @test */
    public function it_can_read_file()
    {
        $file = $this->filesystem->read($date = '2015-01-01');

        static::assertNotEmpty($file);
        static::assertStringStartsWith('['.$date, $file);
    }

    /** @test */
    public function it_can_delete_file()
    {
        $this->createDummyLog($date = date('Y-m-d'));

        // Assert log exists
        $file = $this->filesystem->read($date);

        static::assertNotEmpty($file);

        // Assert log deletion
        try {
            $deleted = $this->filesystem->delete($date);
            $message = '';
        }
        catch (\Exception $e) {
            $deleted = false;
            $message = $e->getMessage();
        }

        static::assertTrue($deleted, $message);
    }

    /** @test */
    public function it_can_get_files()
    {
        $files = $this->filesystem->logs();

        static::assertCount(2, $files);

        foreach ($files as $file) {
            static::assertFileExists($file);
        }
    }

    /** @test */
    public function it_can_set_a_custom_path()
    {
        $this->filesystem->setPath(storage_path('custom-path-logs'));

        $files = $this->filesystem->logs();

        static::assertCount(1, $files);

        foreach ($files as $file) {
            static::assertFileExists($file);
        }
    }


    /** @test */
    public function it_can_get_file_path_by_date()
    {
        static::assertFileExists(
            $this->filesystem->path('2015-01-01')
        );
    }

    /** @test */
    public function it_can_get_dates_from_log_files()
    {
        static::assertDates(
            $this->filesystem->dates()
        );
    }

    /** @test */
    public function it_can_get_dates_with_paths_from_log_files()
    {
        foreach ($this->filesystem->dates(true) as $date => $path) {
            static::assertDate($date);
            static::assertFileExists($path);
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
        static::assertSame(
            'laravel-[0-9][0-9][0-9][0-9]-[0-9][0-9]-[0-9][0-9].log',
            $this->filesystem->getPattern()
        );

        $this->filesystem->setExtension('');

        static::assertSame(
            'laravel-[0-9][0-9][0-9][0-9]-[0-9][0-9]-[0-9][0-9]',
            $this->filesystem->getPattern()
        );

        $this->filesystem->setPrefixPattern('laravel-cli-');

        static::assertSame(
            'laravel-cli-[0-9][0-9][0-9][0-9]-[0-9][0-9]-[0-9][0-9]',
            $this->filesystem->getPattern()
        );

        $this->filesystem->setDatePattern('[0-9][0-9][0-9][0-9]');

        static::assertSame(
            'laravel-cli-[0-9][0-9][0-9][0-9]',
            $this->filesystem->getPattern()
        );

        $this->filesystem->setPattern();

        static::assertSame(
            'laravel-[0-9][0-9][0-9][0-9]-[0-9][0-9]-[0-9][0-9].log',
            $this->filesystem->getPattern()
        );

        $this->filesystem->setPattern(
            'laravel-', '[0-9][0-9][0-9][0-9]-[0-9][0-9]-[0-9][0-9]', '.log'
        );

        static::assertSame(
            'laravel-[0-9][0-9][0-9][0-9]-[0-9][0-9]-[0-9][0-9].log',
            $this->filesystem->getPattern()
        );
    }
}
