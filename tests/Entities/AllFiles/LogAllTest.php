<?php namespace Arcanedev\LogViewer\Tests\Entities\AllFiles;

use Arcanedev\LogViewer\Entities\Log;
use Arcanedev\LogViewer\Tests\TestCase;

/**
 * Class     LogTest
 *
 * @package  Arcanedev\LogViewer\Tests\Entities
 * @author   ARCANEDEV <arcanedev.maroc@gmail.com>
 */
class LogAllTest extends TestCase
{
    /* -----------------------------------------------------------------
     |  Properties
     | -----------------------------------------------------------------
     */

    /** @var  \Arcanedev\LogViewer\Entities\Log */
    private $log;

    private $providedFilenames = array();

    /* -----------------------------------------------------------------
     |  Main Methods
     | -----------------------------------------------------------------
     */

    protected function setUp(): void
    {
        parent::setUp();

        $this->app['config']->set('log-viewer.parse-all-files-in-log-path', true);

        $filename ='laravel-2015-01.log';

        $this->providedFilenames[] = config('log-viewer.storage-path'). DS. 'laravel-2015-01-01.log';
        $this->providedFilenames[] = config('log-viewer.storage-path'). DS. 'laravel-2015-01-02.log';

        $this->log = $this->getLog(config('log-viewer.storage-path'). DS. $filename );
    }

    protected function tearDown(): void
    {
        unset($this->log);

        parent::tearDown();
    }

    /* -----------------------------------------------------------------
     |  Tests
     | -----------------------------------------------------------------
     */

    /** @test */
    public function it_can_be_instantiated()
    {
        $entries = $this->log->entries();

        static::assertInstanceOf(Log::class, $this->log);
        static::assertCount(8, $entries);
        static::assertSame(8, $entries->count());
        static::assertLogEntries($this->log->date, $entries);
    }

    /**
     * @test
     *
     *
     * @param  string  $date
     */
    public function it_can_get_path()
    {
        foreach ($this->providedFilenames as $path) {
            static::assertFileExists($this->getLog($path)->getPath());
        }
    }

    /**
     * @test
     *
     * @param  string  $date
     */
    public function it_can_get_all_entries()
    {
        foreach ($this->providedFilenames as $path) {
            $entries = $this->getLog($path)->entries();

            static::assertCount(8, $entries);
            static::assertSame(8, $entries->count());
            static::assertLogEntries($path, $entries);
        }
    }

    /**
     * @test
     *
     * @param  string  $date
     */
    public function it_can_get_all_entries_by_level()
    {
        foreach ($this->providedFilenames as $path) {
            $log = $this->getLog($path);

            foreach ($this->getLogLevels() as $level) {
                static::assertCount(1, $log->entries($level));
                static::assertLogEntries($path, $log->entries());
            }
        }
    }

    /** @test */
    public function it_can_get_log_stats()
    {
        foreach ($this->log->stats() as $level => $counter) {
            static::assertSame($level === 'all' ? 8 : 1, $counter);
        }
    }

    /**
     * @test
     *
     *
     * @param  string  $date
     */
    public function it_can_get_tree()
    {
        foreach ($this->providedFilenames as $path) {
            $menu = $this->getLog($path)->tree();

            static::assertCount(9, $menu);

            foreach ($menu as $level => $menuItem) {
                if ($level === 'all') {
                    static::assertEquals(8, $menuItem['count']);
                } else {
                    static::assertInLogLevels($level);
                    static::assertInLogLevels($menuItem['name']);
                    static::assertEquals(1, $menuItem['count']);
                }
            }
        }
    }

    /**
     * @test
     *
     * @param  string  $date
     */
    public function it_can_get_translated_menu()
    {
        foreach ($this->providedFilenames as $path) {
            foreach (self::$locales as $locale) {
                $this->app->setLocale($locale);

                $menu = $this->getLog($path)->menu();

                static::assertCount(9, $menu);

                foreach ($menu as $level => $menuItem) {
                    if ($level === 'all') {
                        static::assertSame(8, $menuItem['count']);
                        static::assertTranslatedLevel($locale, $level, $menuItem['name']);
                    } else {
                        static::assertInLogLevels($level);
                        static::assertTranslatedLevel($locale, $level, $menuItem['name']);
                        static::assertSame(1, $menuItem['count']);
                    }
                }
            }
        }
    }

    /** @test */
    public function it_can_convert_to_json()
    {
        static::assertJsonObject($this->log);
    }

}
