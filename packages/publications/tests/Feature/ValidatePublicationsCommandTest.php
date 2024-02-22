<?php

declare(strict_types=1);

namespace Hyde\Publications\Testing\Feature;

use Hyde\Hyde;
use Hyde\Publications\Models\PublicationType;
use Hyde\Testing\TestCase;

/**
 * @covers \Hyde\Publications\Commands\ValidatePublicationsCommand
 * @covers \Hyde\Publications\Actions\PublicationPageValidator
 */
class ValidatePublicationsCommandTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->throwOnConsoleException();
    }

    public function testWithNoPublicationTypes()
    {
        $this->throwOnConsoleException(false);

        $this->artisan('validate:publications')
            ->expectsOutput('Error: No publication types to validate!')
            ->assertExitCode(1);
    }

    public function testWithInvalidPublicationType()
    {
        $this->throwOnConsoleException(false);

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
            ->expectsOutputToContain('Validated 1 publication types, 1 publications, 2 fields')
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

    public function testWithMultipleInvalidFields()
    {
        $this->directory('test-publication');
        $publicationType = new PublicationType('test-publication', fields: [
            ['name' => 'myField', 'type' => 'string'],
            ['name' => 'myNumber', 'type' => 'integer'],
        ]);
        $publicationType->save();

        $this->file('test-publication/my-page.md', <<<'MD'
            ---
            myField: false
            ---

            # My Page
            MD
        );

        $this->artisan('validate:publications')
            ->expectsOutputToContain('Validated 1 publication types, 1 publications, 2 fields')
            ->expectsOutput('Found 0 Warnings')
            ->expectsOutput('Found 2 Errors')
            ->assertExitCode(1);
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

    public function testOutput()
    {
        $this->createFullRangeTestFixtures();

        $this->artisan('validate:publications')
            ->expectsOutputToContain('Validating publications!')
            ->expectsOutput('Validating publication type [test-publication]')
            ->expectsOutput('  ! extra-field.md')
            ->expectsOutput('    ! The extra field is not defined in the publication type.')
            ->expectsOutput('  ⨯ invalid-field-and-extra-field.md')
            ->expectsOutput('    ⨯ The title must be a string.')
            ->expectsOutput('    ! The extra field is not defined in the publication type.')
            ->expectsOutput('  ⨯ invalid-field.md')
            ->expectsOutput('    ⨯ The title must be a string.')
            ->expectsOutput('  ⨯ missing-field.md')
            ->expectsOutput('    ⨯ The title must be a string.')
            ->expectsOutput('  ✓ valid.md')
            ->expectsOutputToContain('Summary:')
            ->expectsOutputToContain('Validated 1 publication types, 5 publications, 7 fields')
            ->expectsOutput('Found 2 Warnings')
            ->expectsOutput('Found 3 Errors')
            ->assertExitCode(1);
    }

    public function testWithVerboseOutput()
    {
        $this->createFullRangeTestFixtures();

        $this->artisan('validate:publications --verbose')
            ->expectsOutputToContain('Validating publications!')
            ->expectsOutput('Validating publication type [test-publication]')
            ->expectsOutput('  ! extra-field.md')
            ->expectsOutput('    ✓ Field title passed.')
            ->expectsOutput('    ! The extra field is not defined in the publication type.')
            ->expectsOutput('  ⨯ invalid-field-and-extra-field.md')
            ->expectsOutput('    ⨯ The title must be a string.')
            ->expectsOutput('    ! The extra field is not defined in the publication type.')
            ->expectsOutput('  ⨯ invalid-field.md')
            ->expectsOutput('    ⨯ The title must be a string.')
            ->expectsOutput('  ⨯ missing-field.md')
            ->expectsOutput('    ⨯ The title must be a string.')
            ->expectsOutput('  ✓ valid.md')
            ->expectsOutput('    ✓ Field title passed.')
            ->expectsOutputToContain('Summary:')
            ->expectsOutputToContain('Validated 1 publication types, 5 publications, 7 fields')
            ->expectsOutput('Found 2 Warnings')
            ->expectsOutput('Found 3 Errors')
            ->assertExitCode(1);
    }

    public function testWithJsonOutput()
    {
        $this->createFullRangeTestFixtures();

        $this->artisan('validate:publications --json')
            ->expectsOutput(<<<'JSON'
                {
                    "test-publication": {
                        "extra-field": {
                            "title": "Field title passed.",
                            "extra": "Warning: The extra field is not defined in the publication type."
                        },
                        "invalid-field-and-extra-field": {
                            "title": "Error: The title must be a string.",
                            "extra": "Warning: The extra field is not defined in the publication type."
                        },
                        "invalid-field": {
                            "title": "Error: The title must be a string."
                        },
                        "missing-field": {
                            "title": "Error: The title must be a string."
                        },
                        "valid": {
                            "title": "Field title passed."
                        }
                    }
                }
                JSON)
            ->assertExitCode(1);
    }

    public function testWithJsonOutputWithNoPublications()
    {
        $this->directory('test-publication');
        $this->copyTestPublicationFixture();

        $this->artisan('validate:publications --json')
            ->expectsOutput(<<<'JSON'
                {
                    "test-publication": []
                }
                JSON
            )
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

    protected function createFullRangeTestFixtures(): void
    {
        $this->directory('test-publication');

        $publicationType = new PublicationType('test-publication', fields: [
            ['name' => 'title', 'type' => 'string'],
        ]);
        $publicationType->save();

        $this->file('test-publication/extra-field.md', <<<'MD'
            ---
            title: foo
            extra: field
            ---

            # My Page
            MD
        );

        $this->file('test-publication/invalid-field-and-extra-field.md', <<<'MD'
            ---
            title: false
            extra: field
            ---

            # My Page
            MD
        );

        $this->file('test-publication/missing-field.md', <<<'MD'
            ---
            ---

            # My Page
            MD
        );

        $this->file('test-publication/invalid-field.md', <<<'MD'
            ---
            title: false
            ---

            # My Page
            MD
        );

        $this->file('test-publication/valid.md', <<<'MD'
            ---
            title: foo
            ---

            # My Page
            MD
        );
    }
}
