<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit;

use Hyde\Support\BuildWarnings;
use Hyde\Testing\UnitTestCase;
use Illuminate\Config\Repository;
use Illuminate\Support\Facades\Config;

use function app;

/**
 * @covers \Hyde\Support\BuildWarnings
 */
class BuildWarningsTest extends UnitTestCase
{
    protected function tearDown(): void
    {
        BuildWarnings::getInstance()->clear();

        parent::tearDown();
    }

    public function testGetInstance()
    {
        $this->assertInstanceOf(BuildWarnings::class, BuildWarnings::getInstance());
    }

    public function testGetInstanceReturnsSingleton()
    {
        $this->assertSame(BuildWarnings::getInstance(), BuildWarnings::getInstance());
    }

    public function testHasWarnings()
    {
        $this->assertFalse(BuildWarnings::hasWarnings());
    }

    public function testGetWarnings()
    {
        $this->assertSame([], BuildWarnings::getWarnings());
    }

    public function testReport()
    {
        BuildWarnings::report('This is a warning');

        $this->assertTrue(BuildWarnings::hasWarnings());
        $this->assertSame(['This is a warning'], BuildWarnings::getWarnings());
    }

    public function testReportsWarnings()
    {
        //
    }

    public function testWriteWarningsToOutput()
    {
        //
    }

    public function testAdd()
    {
        //
    }

    public function testGet()
    {
        //
    }

    public function testClear()
    {
        //
    }

    protected static function mockConfig(array $items = []): void
    {
        app()->bind('config', fn () => new Repository($items));

        Config::swap(app('config'));
    }
}
