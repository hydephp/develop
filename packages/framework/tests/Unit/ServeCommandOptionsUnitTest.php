<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit;

use Hyde\Testing\TestCase;
use Hyde\Console\Commands\ServeCommand;

/**
 * @covers \Hyde\Console\Commands\ServeCommand
 *
 * @see \Hyde\Framework\Testing\Feature\Commands\ServeCommandTest
 */
class ServeCommandOptionsUnitTest extends TestCase
{
    public function test_getHostSelection()
    {
        $this->assertSame('localhost', $this->getMock()->getHostSelection());
    }

    public function test_getHostSelection_withHostOption()
    {
        $this->assertSame('foo', $this->getMock(['host' => 'foo'])->getHostSelection());
    }

    public function test_getHostSelection_withConfigOption()
    {
        $this->app['config']->set('hyde.server.host', 'foo');
        $this->assertSame('foo', $this->getMock()->getHostSelection());
    }

    public function test_getHostSelection_withHostOptionAndConfigOption()
    {
        $this->app['config']->set('hyde.server.host', 'foo');
        $this->assertSame('bar', $this->getMock(['host' => 'bar'])->getHostSelection());
    }

    public function test_getPortSelection()
    {
        $this->assertSame(8080, $this->getMock()->getPortSelection());
    }

    public function test_getPortSelection_withPortOption()
    {
        $this->assertSame(8081, $this->getMock(['port' => 8081])->getPortSelection());
    }

    public function test_getPortSelection_withConfigOption()
    {
        $this->app['config']->set('hyde.server.port', 8082);
        $this->assertSame(8082, $this->getMock()->getPortSelection());
    }

    public function test_getPortSelection_withPortOptionAndConfigOption()
    {
        $this->app['config']->set('hyde.server.port', 8082);
        $this->assertSame(8081, $this->getMock(['port' => 8081])->getPortSelection());
    }

    protected function getMock(array $options = []): ServeCommandMock
    {
        return new ServeCommandMock($options);
    }
}

/**
 * @method getHostSelection
 * @method getPortSelection
 */
class ServeCommandMock extends ServeCommand
{
    public function __construct(array $options = [])
    {
        parent::__construct();

        $this->input = new InputMock($options);
    }

    public function __call($method, $parameters)
    {
        return call_user_func_array([$this, $method], $parameters);
    }

    public function option($key = null)
    {
        return $this->input->getOption($key);
    }
}

class InputMock
{
    protected array $options;

    public function __construct(array $options = [])
    {
        $this->options = $options;
    }

    public function getOption(string $key)
    {
        return $this->options[$key] ?? null;
    }
}
