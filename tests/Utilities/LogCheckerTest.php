<?php

declare(strict_types=1);

namespace Arcanedev\LogViewer\Tests\Utilities;

use Arcanedev\LogViewer\Tests\TestCase;
use Arcanedev\LogViewer\Utilities\LogChecker;

/**
 * Class     LogCheckerTest
 *
 * @package  Arcanedev\LogViewer\Tests\Utilities
 * @author   ARCANEDEV <arcanedev.maroc@gmail.com>
 */
class LogCheckerTest extends TestCase
{
    /* -----------------------------------------------------------------
     |  Properties
     | -----------------------------------------------------------------
     */

    /** @var  \Arcanedev\LogViewer\Utilities\LogChecker */
    private $checker;

    /* -----------------------------------------------------------------
     |  Main Methods
     | -----------------------------------------------------------------
     */

    protected function setUp(): void
    {
        parent::setUp();

        $this->checker = $this->app->make(\Arcanedev\LogViewer\Contracts\Utilities\LogChecker::class);
    }

    protected function tearDown(): void
    {
        unset($this->checker);

        parent::tearDown();
    }

    /* -----------------------------------------------------------------
     |  Tests
     | -----------------------------------------------------------------
     */

    /** @test */
    public function it_can_be_instantiated(): void
    {
        static::assertInstanceOf(LogChecker::class, $this->checker);
    }

    /** @test */
    public function it_must_fails(): void
    {
        static::assertFalse($this->checker->passes());
        static::assertTrue($this->checker->fails());
    }

    /** @test */
    public function it_can_get_messages(): void
    {
        $messages = $this->checker->messages();

        static::assertArrayHasKey('handler', $messages);
        static::assertArrayHasKey('files', $messages);
        static::assertEmpty($messages['handler']);
        static::assertCount(3, $messages['files']);
        static::assertArrayHasKey('laravel.log', $messages['files']);
    }

    /** @test */
    public function it_can_get_requirements(): void
    {
        $requirements = $this->checker->requirements();

        static::assertArrayHasKey('status', $requirements);
        static::assertEquals($requirements['status'], 'success');
        static::assertArrayHasKey('header', $requirements);
        static::assertEquals($requirements['header'], 'Application requirements fulfilled.');
    }

    /** @test */
    public function it_must_fail_the_requirements_on_handler(): void
    {
        config()->set('logging.default', 'stack');

        $requirements = $this->checker->requirements();

        static::assertArrayHasKey('status', $requirements);
        static::assertEquals($requirements['status'], 'failed');
        static::assertArrayHasKey('header', $requirements);
        static::assertEquals($requirements['header'], 'Application requirements failed.');
    }
}
