<?php

declare(strict_types=1);

namespace Hyde\Framework\Testing\Feature\Commands;

use Hyde\Testing\TestCase;
use Illuminate\Support\ServiceProvider;
use NunoMaduro\LaravelConsoleSummary\LaravelConsoleSummaryServiceProvider;

/**
 * @covers \Hyde\Console\Commands\VendorPublishCommand
 */
class VendorPublishCommandTest extends TestCase
{
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
                '<comment>Publish files from all providers and tags listed below</comment>',
                '<fg=gray>Provider:</> ExampleProvider',
                '<fg=gray>Tag:</> example-configs',
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
                '<comment>Publish files from all providers and tags listed below</comment>',
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
                '<comment>Publish files from all providers and tags listed below</comment>',
                '<fg=gray>Tag:</> vendor-configs',
            ])->assertExitCode(0);
    }
}
