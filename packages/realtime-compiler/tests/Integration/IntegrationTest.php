<?php

namespace Hyde\RealtimeCompiler\Tests\Integration;

use PHPUnit\Framework\TestCase;

class IntegrationTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        if (! self::hasTestRunnerSetUp()) {
            self::setUpTestRunner();
        }
    }

    public function testExample()
    {
        $this->assertTrue(true);
    }

    private static function hasTestRunnerSetUp(): bool
    {
        return false;
    }

    private static function setUpTestRunner(): void
    {
        // Set up the test runner
    }
}
