<?php namespace Arcanedev\LogViewer\Tests\Entities\AllFiles;

use Arcanedev\LogViewer\Entities\LogEntryCollection;
use Arcanedev\LogViewer\Tests\TestCase;

/**
 * Class     LogEntryCollectionTest
 *
 * @package  Arcanedev\LogViewer\Tests\Entities
 * @author   ARCANEDEV <arcanedev.maroc@gmail.com>
 */
class LogEntryAllCollectionTest extends TestCase
{
    /* -----------------------------------------------------------------
     |  Properties
     | -----------------------------------------------------------------
     */

    /** @var  \Arcanedev\LogViewer\Entities\LogEntryCollection */
    private $entries;

    /* -----------------------------------------------------------------
     |  Main Methods
     | -----------------------------------------------------------------
     */

    protected function setUp(): void
    {
        parent::setUp();
        $this->app['config']->set('log-viewer.parse-all-files-in-log-path', true);

        $this->entries = new LogEntryCollection;
    }

    protected function tearDown(): void
    {
        unset($this->entries);

        parent::tearDown();
    }

    /* -----------------------------------------------------------------
     |  Tests
     | -----------------------------------------------------------------
     */

    /** @test */
    public function it_can_be_instantiated()
    {
        static::assertInstanceOf(LogEntryCollection::class, $this->entries);
        static::assertCount(0, $this->entries);
        static::assertSame(0, $this->entries->count());
    }

    /** @test */
    public function it_can_load_raw_entries()
    {
        foreach ($this->getFilenames() as $path) {
            $entries = $this->getEntries($path);

            static::assertLogEntries($path, $entries);
            static::assertCount(8, $entries);
            static::assertSame(8, $entries->count());
        }
    }

    /** @test */
    public function it_can_get_entries_by_level()
    {
        foreach ($this->getFilenames() as $path) {
            $this->entries = $this->getEntries($path);

            foreach (self::$logLevels as $level) {
                $entry = $this->entries->filterByLevel($level);

                static::assertLogEntries($path, $entry);
            }
        }
    }

    /** @test */
    public function it_can_get_stats()
    {
        foreach ($this->getFilenames() as $path) {
            $this->entries = $this->getEntries($path);

            $stats = $this->entries->stats();

            foreach ($stats as $level => $count) {
                static::assertSame(($level === 'all') ? 8 : 1, $count);
            }
        }
    }

    /** @test */
    public function it_can_get_tree()
    {
        foreach ($this->getFilenames() as $path) {
            $this->entries = $this->getEntries($path);

            $tree = $this->entries->tree();

            static::assertCount(9, $tree);

            foreach ($tree as $level => $item) {
                static::assertArrayHasKey('name', $item);
                static::assertArrayHasKey('count', $item);

                static::assertEquals($level, $item['name']);
                static::assertSame(($level === 'all') ? 8 : 1, $item['count']);
            }
        }
    }

    /* -----------------------------------------------------------------
     |  Other Methods
     | -----------------------------------------------------------------
     */

    /**
     * Get log entries
     *
     * @param  string  $date
     *
     * @return LogEntryCollection
     */
    private function getEntries($date)
    {
        return (new LogEntryCollection)->load(
            $this->getLogContent($date)
        );
    }
}
