<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Unit;

use Hyde\Hyde;
use Hyde\Testing\TestCase;
use Hyde\Console\Concerns\Command;
use Illuminate\Support\Facades\File;

#[\PHPUnit\Framework\Attributes\CoversClass(\Hyde\Console\Concerns\HasConfigOverrides::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(\Hyde\Console\Commands\BuildSiteCommand::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(\Hyde\Console\Commands\RebuildPageCommand::class)]
class ConfigOverrideOptionTest extends TestCase
{
    protected function tearDown(): void
    {
        File::cleanDirectory(Hyde::path('_site'));

        parent::tearDown();
    }

    public function testConfigOptionCanOverrideAStringValue()
    {
        $this->artisan('build --config=hyde.name=Overridden')
            ->assertExitCode(0);

        $this->assertSame('Overridden', config('hyde.name'));
    }

    public function testConfigOptionCanOverrideABooleanTrueValue()
    {
        config(['hyde.pretty_urls' => false]);

        $this->artisan('build --config=hyde.pretty_urls=true')
            ->assertExitCode(0);

        $this->assertTrue(config('hyde.pretty_urls'));
    }

    public function testConfigOptionCanOverrideABooleanFalseValue()
    {
        config(['hyde.pretty_urls' => true]);

        $this->artisan('build --config=hyde.pretty_urls=false')
            ->assertExitCode(0);

        $this->assertFalse(config('hyde.pretty_urls'));
    }

    public function testConfigOptionCanOverrideANullValue()
    {
        $this->artisan('build --config=hyde.name=null')
            ->assertExitCode(0);

        $this->assertNull(config('hyde.name'));
    }

    public function testConfigOptionCanOverrideAnIntegerValue()
    {
        $this->artisan('build --config=hyde.server.port=1234')
            ->assertExitCode(0);

        $this->assertSame(1234, config('hyde.server.port'));
    }

    public function testConfigOptionCanOverrideAFloatValue()
    {
        $this->artisan('build --config=hyde.custom_float=4.5')
            ->assertExitCode(0);

        $this->assertSame(4.5, config('hyde.custom_float'));
    }

    public function testConfigOptionSupportsNestedDotNotationKeys()
    {
        $this->artisan('build --config=hyde.server.host=example.com')
            ->assertExitCode(0);

        $this->assertSame('example.com', config('hyde.server.host'));
    }

    public function testMultipleConfigOptionsCanBeUsedTogether()
    {
        $this->artisan('build --config=hyde.name=Foo --config=hyde.pretty_urls=true')
            ->assertExitCode(0);

        $this->assertSame('Foo', config('hyde.name'));
        $this->assertTrue(config('hyde.pretty_urls'));
    }

    public function testConfigOptionWithoutEqualsSignIsRejected()
    {
        $this->artisan('build --config=invalid-override')
            ->expectsOutputToContain('Invalid --config value [invalid-override]. Expected format: key=value')
            ->assertExitCode(Command::FAILURE);

        $this->assertFileDoesNotExist(Hyde::path('_site/index.html'));
    }

    public function testConfigOptionTakesPrecedenceOverThePrettyUrlsFlag()
    {
        config(['hyde.pretty_urls' => false]);

        $this->artisan('build --pretty-urls --config=hyde.pretty_urls=false')
            ->assertExitCode(0);

        $this->assertFalse(config('hyde.pretty_urls'));
    }

    public function testRebuildCommandSupportsConfigOption()
    {
        $this->file('_pages/test-page.md', 'foo');

        $this->artisan('rebuild _pages/test-page.md --config=hyde.pretty_urls=true')
            ->assertExitCode(0);

        $this->assertTrue(config('hyde.pretty_urls'));
    }

    public function testRebuildCommandRejectsInvalidConfigOption()
    {
        $this->artisan('rebuild _pages/test-page.md --config=invalid-override')
            ->expectsOutputToContain('Invalid --config value [invalid-override]. Expected format: key=value')
            ->assertExitCode(Command::FAILURE);
    }
}
