<?php

declare(strict_types=1);

namespace Hyde\Publications\Testing\Feature;

use function config;
use function file_put_contents;
use Hyde\Hyde;
use Hyde\Publications\Models\PublicationType;
use Hyde\Testing\TestCase;

/**
 * @covers \Hyde\Publications\Commands\ValidatePublicationsCommand
 */
class ValidatePublicationsCommandTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config(['app.throw_on_console_exception' => true]);
    }

    public function testWithNoPublicationTypes()
    {
        config(['app.throw_on_console_exception' => false]);

        $this->artisan('validate:publications')
            ->expectsOutput('Error: No publication types to validate!')
            ->assertExitCode(1);
    }

    public function testWithInvalidPublicationType()
    {
        config(['app.throw_on_console_exception' => false]);

        $this->artisan('validate:publications', ['publicationType' => 'invalid'])
            ->expectsOutput('Error: Publication type [invalid] does not exist')
            ->assertExitCode(1);
    }

    public function testWithPublicationType()
    {
        $this->directory('test-publication');
        $this->copyTestPublicationFixture();
        $this->file('test-publication/test.md', '---
__createdAt: 2022-11-27 21:07:37
title: My Title
---

## Write something awesome.

');

        $this->artisan('validate:publications')
            ->expectsOutputToContain('Validated 1 publication types, 1 publications, 1 fields')
            ->expectsOutput('Found 0 Warnings')
            ->expectsOutput('Found 0 Errors')
            ->assertExitCode(0);
    }

    public function testWithPublicationTypeAndVerboseOutput()
    {
        $this->directory('test-publication');
        $this->copyTestPublicationFixture();
        $this->file('test-publication/test.md', '---
__createdAt: 2022-11-27 21:07:37
title: My Title
---

## Write something awesome.

');

        $this->artisan('validate:publications', ['--verbose' => true])
             ->expectsOutputToContain('Validated 1 publication types, 1 publications, 1 fields')
             ->expectsOutput('Found 0 Warnings')
             ->expectsOutput('Found 0 Errors')
             ->assertExitCode(0);
    }

    public function testWithInvalidPublication()
    {
        $this->directory('test-publication');
        $this->copyTestPublicationFixture();
        file_put_contents(Hyde::path('test-publication/test.md'), '---
---

Hello World
');

        $this->artisan('validate:publications')
             ->expectsOutputToContain('Validated 1 publication types, 1 publications, 1 fields')
             ->expectsOutput('Found 0 Warnings')
             ->expectsOutput('Found 1 Errors')
             ->assertExitCode(1);
    }

    public function testWithWarnedPublication()
    {
        $this->directory('test-publication');
        $this->copyTestPublicationFixture();
        file_put_contents(Hyde::path('test-publication/test.md'), '---
title: foo
extra: field
---

Hello World
');

        $this->artisan('validate:publications')
            ->expectsOutputToContain('Validated 1 publication types, 1 publications, 1 fields')
            ->expectsOutput('Found 1 Warnings')
            ->expectsOutput('Found 0 Errors')
            ->assertExitCode(0);
    }

    public function testWithMultiplePublicationTypes()
    {
        $this->directory('test-publication');
        $this->directory('test-publication-two');
        $this->savePublication('Test Publication');
        $this->savePublication('Test Publication Two');

        $this->artisan('validate:publications')
            ->assertExitCode(0);
    }

    public function testOnlySpecifiedTypeIsValidatedWhenUsingArgument()
    {
        $this->directory('test-publication');
        $this->directory('test-publication-two');
        $this->savePublication('Test Publication');
        $this->savePublication('Test Publication Two');

        $this->artisan('validate:publications test-publication-two')
            ->assertExitCode(0);
    }

    public function testWithJsonOutput()
    {
        $this->directory('test-publication');
        $this->copyTestPublicationFixture();

        $this->artisan('validate:publications --json')
            ->expectsOutput(<<<'JSON'
                {
                    "$publicationTypes": {
                        "test-publication": []
                    }
                }
                JSON)
            ->assertExitCode(0);
    }

    protected function copyTestPublicationFixture(): void
    {
        file_put_contents(Hyde::path('test-publication/schema.json'), <<<'JSON'
            {
                "name": "Test Publication",
                "canonicalField": "title",
                "detailTemplate": "detail.blade.php",
                "listTemplate": "list.blade.php",
                "sortField": "__createdAt",
                "sortAscending": true,
                "pageSize": 25,
                "fields": [
                    {
                        "name": "title",
                        "type": "string"
                    }
                ]
            }
            JSON
        );
    }

    protected function savePublication(string $name): void
    {
        (new PublicationType($name))->save();
    }
}
