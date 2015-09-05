<?php namespace Arcanedev\LogViewer\Tests\Utilities;

use Arcanedev\LogViewer\Tests\TestCase;
use Arcanedev\LogViewer\Utilities\LogMenu;

/**
 * Class     LogMenuTest
 *
 * @package  Arcanedev\LogViewer\Tests\Utilities
 * @author   ARCANEDEV <arcanedev.maroc@gmail.com>
 */
class LogMenuTest extends TestCase
{
    /* ------------------------------------------------------------------------------------------------
     |  Properties
     | ------------------------------------------------------------------------------------------------
     */
    /** @var LogMenu */
    private $menu;

    /* ------------------------------------------------------------------------------------------------
     |  Main Functions
     | ------------------------------------------------------------------------------------------------
     */
    public function setUp()
    {
        parent::setUp();

        $this->menu = $this->app['log-viewer.menu'];
    }

    public function tearDown()
    {
        parent::tearDown();

        unset($this->menu);
    }

    /* ------------------------------------------------------------------------------------------------
     |  Test Functions
     | ------------------------------------------------------------------------------------------------
     */
    /** @test */
    public function it_can_be_instantiated()
    {
        $this->assertInstanceOf(LogMenu::class, $this->menu);
    }

    /**
     * @test
     *
     * @dataProvider provideMenuItems
     */
    public function it_can_make_menu_items($level, $dummyItem)
    {
        list($name, $count) = array_values($dummyItem);
        $item               = $this->menu->item($name, $count);

        $this->assertArrayHasKey('name', $item);
        $this->assertEquals($name, $item['name']);
        $this->assertArrayHasKey('count', $item);
        $this->assertEquals($count, $item['count']);

        $this->assertCount(2, $item);
        $this->assertEquals($level, $item['name']);
        $this->assertArrayNotHasKey('icon', $item);
    }

    /**
     * @test
     *
     * @dataProvider provideMenuItems
     */
    public function it_can_make_menu_items_with_icons($level, $dummyItem)
    {
        list($name, $count) = array_values($dummyItem);
        $item               = $this->menu->item($name, $count, false, true);

        $this->assertCount(3, $item);
        $this->assertEquals($level, $item['name']);
        $this->assertMenuItem($item, $name, $count);
    }

    /**
     * @test
     *
     * @dataProvider provideMenuItems
     *
     * @param  string  $level
     * @param  array   $dummyItems
     */
    public function it_can_make_menu_items_with_trans($level, $dummyItems)
    {
        foreach (self::$locales as $locale) {
            $this->app->setLocale($locale);

            list($name, $count) = array_values($this->transMenuItem($dummyItems, $locale));
            $item               = $this->menu->item($level, $count, true);

            $this->assertNotEquals($level, $item['name']);
            $this->assertMenuItem($item, $name, $count, false);
        }
    }

    /* ------------------------------------------------------------------------------------------------
     |  Data provider functions
     | ------------------------------------------------------------------------------------------------
     */
    /**
     * Provide menu items
     *
     * @return array
     */
    public function provideMenuItems()
    {
        return $this->getDummyMenuItems();
    }

    /* ------------------------------------------------------------------------------------------------
     |  Other Functions
     | ------------------------------------------------------------------------------------------------
     */
    /**
     * @param  array   $items
     *
     * @return array
     */
    private function transMenuItem($items, $locale)
    {
        $items['name'] = $this->trans()
            ->get('log-viewer::levels.' . $items['name'], [], $locale);

        return $items;
    }
}
