<?php

namespace Hyde\Framework\Testing\Feature;

use Hyde\Framework\Services\BuildService;
use Hyde\Testing\ResetsApplication;
use Hyde\Testing\TestCase;
use Illuminate\Console\OutputStyle;
use Mockery;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

/**
 * @covers \Hyde\Framework\Services\BuildService
 */
class BuildServiceTest extends TestCase
{
    use ResetsApplication;

    protected function setUp(): void
    {
        parent::setUp();
        $this->resetSite();
    }

    protected function tearDown(): void
    {
        $this->resetSite();
        parent::tearDown();
    }

    public function test_build_service()
    {
        $service = $this->makeService();

        $this->assertInstanceOf(BuildService::class, $service);
    }

    protected function makeService(): BuildService
    {
        return new BuildService($this->mockOutputInterface());
    }

    protected function mockOutputInterface()
    {
        $this->app->bind(OutputStyle::class, function () {
            return Mockery::mock(OutputStyle::class.'[askQuestion]', [
                (new ArrayInput([])), Mockery::mock(BufferedOutput::class.'[doWrite]')
                    ->shouldAllowMockingProtectedMethods()
                    ->shouldIgnoreMissing(),
            ]);
        });

       return $this->app->make(OutputStyle::class);
    }
}
