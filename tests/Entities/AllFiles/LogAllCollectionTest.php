<?php namespace Arcanedev\LogViewer\Tests\Entities\AllFiles;

use Arcanedev\LogViewer\Entities\LogCollection;
use Arcanedev\LogViewer\Tests\TestCase;

/**
 * Class     LogCollectionTest
 *
 * @package  Arcanedev\LogViewer\Tests\Entities
 * @author   ARCANEDEV <arcanedev.maroc@gmail.com>
 */
class LogAllCollectionTest extends TestCase
{
    /* -----------------------------------------------------------------
     |  Properties
     | -----------------------------------------------------------------
     */

    /** @var  \Arcanedev\LogViewer\Entities\LogCollection */
    private $logs;

    /* -----------------------------------------------------------------
     |  Main Methods
     | -----------------------------------------------------------------
     */

    protected function setUp(): void
    {
        parent::setUp();

        $this->app['config']->set('log-viewer.parse-all-files-in-log-path', true);

        $this->logs = LogCollection::make();
    }

    protected function tearDown(): void
    {
        unset($this->logs);

        parent::tearDown();
    }

    /* -----------------------------------------------------------------
     |  Tests
     | -----------------------------------------------------------------
     */

    /** @test */
    public function it_can_be_instantiated()
    {
        static::assertInstanceOf(LogCollection::class, $this->logs);
    }

    /** @test */
    public function it_can_get_all_logs()
    {
        static::assertCount(5,   $this->logs);
        static::assertSame(5,  $this->logs->count());
        static::assertSame(40, $this->logs->total());

        foreach ($this->logs as $filename => $log) {
            static::assertLog($log, config('log-viewer.storage-path'). DS. $filename);
            static::assertCount(8,  $log->entries());
            static::assertSame(8, $log->entries()->count());
        }
    }

    /** @test */
    public function it_can_get_a_log_by_filename()
    {
        $filename ='laravel-2015-01.log';
        $log = $this->logs->get($filename);

        static::assertLog($log, config('log-viewer.storage-path'). DS. $filename);
        static::assertCount(8, $log->entries());
        static::assertSame(8, $log->entries()->count());
    }

    /** @test */
    public function it_can_get_the_log_entries_by_filename()
    {
        $filename ='laravel-2015-01.log';
        $entries = $this->logs->entries($filename );

        static::assertLogEntries(config('log-viewer.storage-path'). DS. $filename, $entries);
        static::assertCount(8, $entries);
        static::assertSame(8, $entries->count());
    }

    /** @test */
    public function it_can_get_the_log_entries_by_filename_and_level()
    {
        $filename ='laravel-2015-01.log';
        $path = config('log-viewer.storage-path'). DS. $filename;

        foreach (self::$logLevels as $level) {
            $entries = $this->logs->entries($filename , $level);

            static::assertLogEntries($path , $entries);
            static::assertCount(1, $entries);
            static::assertSame(1, $entries->count());
        }

        $entries = $this->logs->entries($filename, 'all');

        static::assertLogEntries($path, $entries);
        static::assertCount(8, $entries);
        static::assertSame(8, $entries->count());
    }


    /** @test */
    public function it_can_get_logs_stats()
    {
        $stats = $this->logs->stats();

        foreach ($stats as $path => $counters) {

            foreach ($counters as $level => $counter) {
                if ($level === 'all') {
                    static::assertSame(8, $counter);
                }
                else {
                    static::assertEquals(1, $counter);
                }
            }
        }
    }

    /** @test */
    public function it_can_get_log_tree()
    {
        $tree = $this->logs->tree();

        static::assertCount(5, $tree);

        foreach ($tree as $path => $levels) {

            foreach ($levels as $level => $item) {
                static::assertEquals($level, $item['name']);
                static::assertSame($level === 'all' ? 8 : 1, $item['count']);
            }
        }
    }

    /** @test */
    public function it_can_get_log_menu()
    {
        foreach(self::$locales as $locale) {
            $this->app->setLocale($locale);
            $menu = $this->logs->menu();

            foreach ($menu as $path => $levels) {

                foreach ($levels as $level => $item) {
                    static::assertNotEquals($level, $item['name']);
                    static::assertTranslatedLevel($locale, $level, $item['name']);
                    static::assertSame($level == 'all' ? 8 : 1, $item['count']);
                }
            }
        }
    }

    /** @test */
    public function it_must_throw_a_log_not_found_on_get_method()
    {
        $this->expectException(\Arcanedev\LogViewer\Exceptions\LogNotFoundException::class);
        $this->expectExceptionMessage('Log not found in this date [2222-01-01]');

        $this->logs->get('2222-01-01');
    }

    /** @test */
    public function it_must_throw_a_log_not_found_on_log_method()
    {
        $this->expectException(\Arcanedev\LogViewer\Exceptions\LogNotFoundException::class);
        $this->expectExceptionMessage('Log not found in this date [2222-01-01]');

        $this->logs->log('2222-01-01');
    }
}
