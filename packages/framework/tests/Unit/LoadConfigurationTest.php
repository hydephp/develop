<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit;

use Hyde\Foundation\Application;
use Hyde\Foundation\Internal\LoadConfiguration;
use Hyde\Testing\UnitTestCase;

/**
 * @covers \Hyde\Foundation\Internal\LoadConfiguration
 */
class LoadConfigurationTest extends UnitTestCase
{
    public function testItLoadsRuntimeConfiguration()
    {
        $serverBackup = $_SERVER;

        $_SERVER['argv'] = ['--pretty-urls', '--no-api'];

        $app = new Application(getcwd());

        $loader = new LoadConfiguration();
        $loader->bootstrap($app);

        $this->assertTrue(config('hyde.pretty_urls'));
        $this->assertFalse(config('hyde.api_calls'));

        $_SERVER = $serverBackup;

        $loader->bootstrap($app);
        $this->assertFalse(config('hyde.pretty_urls'));
        $this->assertNull(config('hyde.api_calls'));
    }

    public function testItLoadsRealtimeCompilerEnvironmentConfiguration()
    {
        (new LoadConfigurationEnvironmentTestClass(['HYDE_RC_SERVER_DASHBOARD' => 'enabled']))->bootstrap(new Application(getcwd()));
        $this->assertTrue(config('hyde.server.dashboard.enabled'));

        (new LoadConfigurationEnvironmentTestClass(['HYDE_RC_SERVER_DASHBOARD' => 'disabled']))->bootstrap(new Application(getcwd()));
        $this->assertFalse(config('hyde.server.dashboard.enabled'));
    }
}

class LoadConfigurationEnvironmentTestClass extends LoadConfiguration
{
    protected array $env;

    public function __construct(array $env)
    {
        $this->env = $env;
    }

    protected function getEnv(string $name): string|false|null
    {
        return $this->env[$name];
    }
}
