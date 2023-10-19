<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature\Commands;

use Hyde\Console\Commands\VendorPublishCommand;
use Hyde\Hyde;
use Hyde\Testing\TestCase;
use Illuminate\Console\View\Components\Factory;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\ServiceProvider;
use NunoMaduro\LaravelConsoleSummary\LaravelConsoleSummaryServiceProvider;

/**
 * @covers \Hyde\Console\Commands\VendorPublishCommand
 */
class VendorPublishCommandTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->originalPublishers = ServiceProvider::$publishes;
        $this->originalGroups = ServiceProvider::$publishGroups;
    }

    protected function tearDown(): void
    {
        ServiceProvider::$publishes = $this->originalPublishers;
        ServiceProvider::$publishGroups = $this->originalGroups;

        parent::tearDown();
    }

    public function test_command_prompts_for_provider_or_tag()
    {
        ServiceProvider::$publishes = [
            'ExampleProvider' => '',
        ];
        ServiceProvider::$publishGroups = [
            'example-configs' => [],
        ];

        $this->artisan('vendor:publish')
            ->expectsChoice('Which provider or tag\'s files would you like to publish?', 'Tag: example-configs', [
                '<fg=gray>Provider:</> ExampleProvider',
                '<fg=gray>Tag:</> example-configs',
                'All providers and tags',
            ])
            ->assertExitCode(0);
    }

    public function test_unhelpful_publishers_are_removed()
    {
        ServiceProvider::$publishes = [
            LaravelConsoleSummaryServiceProvider::class => '',
        ];
        ServiceProvider::$publishGroups = [];

        $this->artisan('vendor:publish')
            ->expectsChoice('Which provider or tag\'s files would you like to publish?', 'Tag: example-configs', [
                'All providers and tags',
            ])->assertExitCode(0);
    }

    public function test_config_group_is_renamed_to_be_more_helpful()
    {
        ServiceProvider::$publishes = [];
        ServiceProvider::$publishGroups = [
            'config' => [],
        ];

        $this->artisan('vendor:publish')
            ->expectsChoice('Which provider or tag\'s files would you like to publish?', 'Tag: vendor-configs', [
                'All providers and tags',
                '<fg=gray>Tag:</> vendor-configs',
            ])->assertExitCode(0);
    }

    public function test_status_method()
    {
        $command = new StatusMethodTestClass($this->createMock(Filesystem::class));

        $components = $this->mock(Factory::class);
        $components->shouldReceive('task')
            ->once()->with('Copying config [config/hyde.php] to [config/hyde.php]');

        $command->setMockObject($components);
        $command->status(Hyde::path('config/hyde.php'), 'config/hyde.php', 'config');
    }
}

class StatusMethodTestClass extends VendorPublishCommand
{
    public function status($from, $to, $type): void
    {
        parent::status($from, $to, $type);
    }

    public function setMockObject($mock)
    {
        $this->components = $mock;
    }
}
