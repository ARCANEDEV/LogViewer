<?php namespace Arcanedev\LogViewer\Tests;

use Arcanedev\LogViewer\Contracts\TableInterface;
use Arcanedev\LogViewer\Entities\Log;
use Arcanedev\LogViewer\Entities\LogEntry;
use Arcanedev\LogViewer\Entities\LogEntryCollection;
use Carbon\Carbon;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Foundation\Application;
use Orchestra\Testbench\TestCase as BaseTestCase;
use Psr\Log\LogLevel;
use ReflectionClass;

/**
 * Class     TestCase
 *
 * @package  Arcanedev\LogViewer\Tests
 * @author   ARCANEDEV <arcanedev.maroc@gmail.com>
 */
abstract class TestCase extends BaseTestCase
{
    /* ------------------------------------------------------------------------------------------------
     |  Properties
     | ------------------------------------------------------------------------------------------------
     */
    /** @var array */
    protected static $logLevels;

    protected static $locales = ['ar', 'de', 'en', 'es', 'fr', 'hy', 'it', 'nl', 'pl', 'pt-BR', 'ro', 'ru', 'sv', 'tr', 'zh'];

    /* ------------------------------------------------------------------------------------------------
     |  Main functions
     | ------------------------------------------------------------------------------------------------
     */
    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        static::$logLevels = self::getLogLevels();
    }

    public static function tearDownAfterClass()
    {
        parent::tearDownAfterClass();

        static::$logLevels = [];
    }

    /* ------------------------------------------------------------------------------------------------
     |  Bench Functions
     | ------------------------------------------------------------------------------------------------
     */
    /**
     * Get package providers.
     *
     * @param  Application  $app
     *
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [
            'Arcanedev\\LogViewer\\LogViewerServiceProvider'
        ];
    }

    /**
     * Get package aliases.
     *
     * @param  Application  $app
     *
     * @return array
     */
    protected function getPackageAliases($app)
    {
        return [
            // 'LogViewer' => \Arcanedev\LogViewer\Facades\LogViewer::class
        ];
    }

    /**
     * Define environment setup.
     *
     * @param  Application  $app
     */
    protected function getEnvironmentSetUp($app)
    {
        $app['path.storage'] = realpath(__DIR__ . '/fixtures');

        /** @var \Illuminate\Config\Repository $config */
        $config = $app['config'];

        $config->set('log-viewer.storage-path', $app['path.storage'] . DS  .'logs');
    }

    /* ------------------------------------------------------------------------------------------------
     |  Custom assertions
     | ------------------------------------------------------------------------------------------------
     */
    /**
     * Asserts that a string is a valid JSON string.
     *
     * @param  Jsonable|mixed  $object
     * @param  string          $message
     */
    public static function assertJsonObject($object, $message = '')
    {
        self::assertInstanceOf('Illuminate\\Contracts\\Support\\Jsonable', $object);
        self::assertJson($object->toJson(JSON_PRETTY_PRINT), $message);

        self::assertInstanceOf('JsonSerializable', $object);
        self::assertJson(json_encode($object, JSON_PRETTY_PRINT), $message);
    }

    /**
     * Assert Log object
     *
     * @param  Log     $log
     * @param  string  $date
     */
    protected function assertLog(Log $log, $date)
    {
        $this->assertEquals($date, $log->date);
        $this->assertLogEntries($log->date, $log->entries());
    }

    /**
     * Assert Log entries object
     *
     * @param  string              $date
     * @param  LogEntryCollection  $entries
     */
    protected function assertLogEntries($date, LogEntryCollection $entries)
    {
        foreach ($entries as $entry) {
            $this->assertLogEntry($date, $entry);
        }
    }

    /**
     * Assert log entry object
     *
     * @param  string    $date
     * @param  LogEntry  $entry
     */
    protected function assertLogEntry($date, LogEntry $entry)
    {
        $dt = Carbon::createFromFormat('Y-m-d', $date);

        $this->assertInLogLevels($entry->level);
        $this->assertInstanceOf('Carbon\\Carbon', $entry->datetime);
        $this->assertTrue($entry->datetime->isSameDay($dt));
        $this->assertNotEmpty($entry->header);
        $this->assertNotEmpty($entry->stack);
    }

    /**
     * Assert in log levels
     *
     * @param  string  $level
     * @param  string  $message
     */
    protected function assertInLogLevels($level, $message = '')
    {
        $this->assertContains($level, self::$logLevels, $message);
    }

    /**
     * Assert levels
     *
     * @param  array  $levels
     */
    protected function assertLevels(array $levels)
    {
        $this->assertCount(8, $levels);

        foreach ($this->getLogLevels() as $key => $value) {
            $this->assertArrayHasKey($key, $levels);
            $this->assertEquals($value, $levels[$key]);
        }
    }

    /**
     * Assert translated level
     *
     * @param  string  $locale
     * @param  array   $levels
     */
    protected function assertTranslatedLevels($locale, $levels)
    {
        foreach ($levels as $level => $translatedLevel) {
            $this->assertTranslatedLevel($locale, $level, $translatedLevel);
        }
    }

    /**
     * Assert translated level
     *
     * @param  string  $locale
     * @param  string  $level
     * @param  string  $actualTrans
     */
    protected function assertTranslatedLevel($locale, $level, $actualTrans)
    {
        $expected = $this->getTranslatedLevel($locale, $level);

        $this->assertEquals($expected, $actualTrans);
    }

    /**
     * Assert dates
     *
     * @param  array   $dates
     * @param  string  $message
     */
    public function assertDates(array $dates, $message = '')
    {
        foreach ($dates as $date) {
            $this->assertDate($date, $message);
        }
    }

    /**
     * Assert date [YYYY-MM-DD]
     *
     * @param  string  $date
     * @param  string  $message
     */
    public function assertDate($date, $message = '')
    {
        $this->assertRegExp('/' . REGEX_DATE_PATTERN . '/', $date, $message);
    }

    /**
     * Assert Menu item.
     *
     * @param  array      $item
     * @param  string     $name
     * @param  int        $count
     * @param  bool|true  $withIcons
     */
    protected function assertMenuItem($item, $name, $count, $withIcons = true)
    {
        $this->assertArrayHasKey('name', $item);
        $this->assertEquals($name, $item['name']);
        $this->assertArrayHasKey('count', $item);
        $this->assertEquals($count, $item['count']);

        if ($withIcons) {
            $this->assertArrayHasKey('icon', $item);
            $this->assertStringStartsWith('fa fa-fw fa-', $item['icon']);
        }
        else {
            $this->assertArrayNotHasKey('icon', $item);
        }
    }

    /**
     * Assert table instance.
     *
     * @param  TableInterface  $table
     */
    protected function assertTable(TableInterface $table)
    {
        $this->assertTableHeader($table);
        $this->assertTableRows($table);
        $this->assertTableFooter($table);
    }

    /**
     * Assert table header.
     *
     * @param  TableInterface  $table
     */
    protected function assertTableHeader(TableInterface $table)
    {
        $header = $table->header();

        $this->assertCount(10, $header);
        // TODO: Add more assertions to check the content
    }

    /**
     * Assert table rows.
     *
     * @param  TableInterface  $table
     */
    protected function assertTableRows(TableInterface $table)
    {
        foreach ($table->rows() as $date => $row) {
            $this->assertDate($date);
            $this->assertCount(10, $row);

            foreach ($row as $key => $value) {
                switch ($key) {
                    case 'date':
                        $this->assertDate($value);
                        break;

                    case 'all':
                        $this->assertEquals(8, $value);
                        break;

                    default:
                        $this->assertEquals(1, $value);
                        break;
                }
            }
        }
    }

    /**
     * Assert table footer.
     *
     * @param  TableInterface  $table
     */
    protected function assertTableFooter(TableInterface $table)
    {
        foreach ($table->footer() as $key => $value) {
            $this->assertEquals($key === 'all' ? 16 : 2, $value);
        }
    }

    /**
     * Assert HEX Color
     *
     * @param  string  $color
     * @param  string  $message
     */
    protected function assertHexColor($color, $message = '')
    {
        $pattern = '/^#?([a-f0-9]{3}|[a-f0-9]{6})$/i';

        $this->assertRegExp($pattern, $color, $message);
    }

    /* ------------------------------------------------------------------------------------------------
     |  Other Functions
     | ------------------------------------------------------------------------------------------------
     */
    /**
     * Get Illuminate Filesystem
     *
     * @return \Illuminate\Filesystem\Filesystem
     */
    public function illuminateFile()
    {
        return app('files');
    }

    /**
     * Get Filesystem utility
     *
     * @return \Arcanedev\LogViewer\Utilities\Filesystem
     */
    protected function filesystem()
    {
        return app('arcanedev.log-viewer.filesystem');
    }

    /**
     * Get translator repository
     *
     * @return \Illuminate\Translation\Translator
     */
    protected function trans()
    {
        return $this->app['translator'];
    }

    /**
     * Get config repository
     *
     * @return \Illuminate\Config\Repository
     */
    protected function config()
    {
        return $this->app['config'];
    }

    /**
     * Get log path
     *
     * @param  string $date
     *
     * @return string
     */
    public function getLogPath($date)
    {
        return $this->filesystem()->path($date);
    }

    /**
     * Get log content
     *
     * @param  string  $date
     *
     * @return string
     *
     * @throws \Arcanedev\LogViewer\Exceptions\FilesystemException
     */
    public function getLogContent($date)
    {
        return $this->filesystem()->read($date);
    }

    /**
     * Get logs dates
     *
     * @return array
     */
    public function getDates()
    {
        return $this->filesystem()->dates();
    }

    /**
     * Get log object from fixture
     *
     * @param  string  $date
     *
     * @return Log
     */
    protected function getLog($date)
    {
        $path = $this->getLogPath($date);
        $raw  = $this->getLogContent($date);

        return Log::make($date, $path, $raw);
    }

    /**
     * Get random entry from a log file.
     *
     * @param  string  $date
     *
     * @return mixed
     */
    protected function getRandomLogEntry($date)
    {
        return $this->getLog($date)
            ->entries()
            ->random(1);
    }

    /**
     * Get log levels.
     *
     * @return array
     */
    protected static function getLogLevels()
    {
        $class = new ReflectionClass(new LogLevel);

        return self::$logLevels = $class->getConstants();
    }

    /**
     * Create dummy log.
     *
     * @param  string  $date
     *
     * @return bool
     */
    protected function createDummyLog($date)
    {
        return copy(
            storage_path('dummy.log'),                // Source
            storage_path("/logs/laravel-{$date}.log") // Destination
        );
    }

    /**
     * Get translated level.
     *
     * @param  string  $locale
     * @param  string  $key
     *
     * @return mixed
     */
    private function getTranslatedLevel($locale, $key)
    {
        return array_get(
            $this->getTranslatedLevels(),
            "$locale.$key"
        );
    }

    /**
     * Get translated levels.
     *
     * @return array
     */
    protected function getTranslatedLevels()
    {
        $translator = $this->app['translator'];

        return array_map(function ($locale) use ($translator) {
            return $translator->get('log-viewer::levels', [], $locale);
        }, array_combine(self::$locales, self::$locales));
    }

    /**
     * Get config path
     *
     * @return string
     */
    protected function getConfigPath()
    {
        return realpath(config_path());
    }
}
