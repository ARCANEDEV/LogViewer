<?php

declare(strict_types=1);

namespace Arcanedev\LogViewer\Tests\Entities;

use Arcanedev\LogViewer\Entities\LogEntryCollection;
use Arcanedev\LogViewer\Tests\TestCase;

/**
 * Class     LogEntryCollectionTest
 *
 * @author   ARCANEDEV <arcanedev.maroc@gmail.com>
 */
class LogEntryCollectionTest extends TestCase
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
    public function it_can_be_instantiated(): void
    {
        static::assertInstanceOf(LogEntryCollection::class, $this->entries);
        static::assertCount(0, $this->entries);
        static::assertSame(0, $this->entries->count());
    }

    /** @test */
    public function it_can_load_raw_entries(): void
    {
        foreach ($this->getDates() as $date) {
            $entries = $this->getEntries($date);

            static::assertLogEntries($date, $entries);
            static::assertCount(8, $entries);
            static::assertSame(8, $entries->count());
        }
    }

    /** @test */
    public function it_can_get_entries_by_level(): void
    {
        foreach ($this->getDates() as $date) {
            $this->entries = $this->getEntries($date);

            foreach (self::$logLevels as $level) {
                $entry = $this->entries->filterByLevel($level);

                static::assertLogEntries($date, $entry);
            }
        }
    }

    /** @test */
    public function it_can_get_stats(): void
    {
        foreach ($this->getDates() as $date) {
            $this->entries = $this->getEntries($date);

            $stats = $this->entries->stats();

            foreach ($stats as $level => $count) {
                static::assertSame(($level === 'all') ? 8 : 1, $count);
            }
        }
    }

    /** @test */
    public function it_can_get_tree(): void
    {
        foreach ($this->getDates() as $date) {
            $this->entries = $this->getEntries($date);

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
     * @return \Arcanedev\LogViewer\Entities\LogEntryCollection
     */
    private function getEntries($date)
    {
        return (new LogEntryCollection)->load(
            $this->getLogContent($date)
        );
    }
}
