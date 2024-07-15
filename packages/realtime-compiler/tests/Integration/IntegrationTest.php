<?php

namespace Hyde\RealtimeCompiler\Tests\Integration;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class IntegrationTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        $monorepo = realpath(__DIR__.'/../../../');

        if ($monorepo && file_exists($monorepo.'/hyde')) {
            throw new InvalidArgumentException('This test suite is not intended to be run from the monorepo.');
        }

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
