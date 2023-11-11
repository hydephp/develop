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
        $app = new Application(getcwd());

        $loader = new LoadConfigurationTestClass(['--pretty-urls', '--no-api']);
        $loader->bootstrap($app);

        $this->assertTrue(config('hyde.pretty_urls'));
        $this->assertFalse(config('hyde.api_calls'));

        $loader = new LoadConfigurationTestClass([]);
        $loader->bootstrap($app);

        $this->assertFalse(config('hyde.pretty_urls'));
        $this->assertNull(config('hyde.api_calls'));
    }

    public function testItLoadsRealtimeCompilerEnvironmentConfiguration()
    {
        (new LoadConfigurationEnvironmentTestClass(['HYDE_SERVER_DASHBOARD' => 'enabled']))->bootstrap(new Application(getcwd()));
        $this->assertTrue(config('hyde.server.dashboard.enabled'));

        (new LoadConfigurationEnvironmentTestClass(['HYDE_SERVER_DASHBOARD' => 'disabled']))->bootstrap(new Application(getcwd()));
        $this->assertFalse(config('hyde.server.dashboard.enabled'));
    }
}

class LoadConfigurationTestClass extends LoadConfiguration
{
    protected array $argv;

    public function __construct(array $argv)
    {
        $this->argv = $argv;
    }

    protected function getArgv(): ?array
    {
        return $this->argv;
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
