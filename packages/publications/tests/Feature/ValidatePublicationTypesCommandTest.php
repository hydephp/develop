<?php

declare(strict_types=1);

namespace Hyde\Publications\Testing\Feature;

use Hyde\Publications\Models\PublicationType;
use Hyde\Testing\TestCase;

/**
 * @covers \Hyde\Publications\Commands\ValidatePublicationTypesCommand
 */
class ValidatePublicationTypesCommandTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->throwOnConsoleException();
    }

    public function testWithNoPublicationTypes()
    {
        $this->throwOnConsoleException(false);

        $this->artisan('validate:publicationTypes')
            ->expectsOutputToContain('Validating publication schemas!')
            ->expectsOutput('Error: No publication types to validate!')
            ->assertExitCode(1);
    }

    public function testWithValidSchemaFile()
    {
        $this->throwOnConsoleException(false);

        $this->directory('test-publication');
        $publicationType = new PublicationType('test-publication', fields: [
            ['name' => 'myField', 'type' => 'string'],
        ]);
        $publicationType->save();

        $this->artisan('validate:publicationTypes')
            ->expectsOutputToContain('Validating publication schemas!')
            ->expectsOutput('Validating schema file for [test-publication]')
            ->expectsOutput('  No top-level schema errors found')
            ->expectsOutput('  No field-level schema errors found')
            ->expectsOutputToContain('All done')
            ->assertExitCode(0);
    }

    public function testWithInvalidSchemaFile()
    {
        $this->directory('test-publication');
        $publicationType = new PublicationType('test-publication');
        $publicationType->save();

        $this->file('test-publication/schema.json', <<<'JSON'
            {
                "name": 123,
                "canonicalField": 123,
                "detailTemplate": 123,
                "listTemplate": 123,
                "sortField": 123,
                "sortAscending": 123,
                "pageSize": "123",
                 "fields": [
                    {
                        "name": 123,
                        "type": 123
                    },
                    {
                        "noName": "myField",
                        "noType": "string"
                    }
                ],
                "directory": "foo"
            }
            JSON
        );

        $this->artisan('validate:publicationTypes')
            ->expectsOutputToContain('Validating publication schemas!')
            ->expectsOutput('Validating schema file for [test-publication]')
            ->expectsOutput('  Found 7 top-level schema errors:')
            ->expectsOutput('    x The name must be a string.')
            ->expectsOutput('    x The canonical field must be a string.')
            ->expectsOutput('    x The detail template must be a string.')
            ->expectsOutput('    x The list template must be a string.')
            ->expectsOutput('    x The sort field must be a string.')
            ->expectsOutput('    x The sort ascending field must be true or false.')
            ->expectsOutput('    x The directory field is prohibited.')
            ->expectsOutput('  Found errors in 2 field definitions:')
            ->expectsOutput('    Field #1:')
            ->expectsOutput('      x The type must be a string.')
            ->expectsOutput('      x The name must be a string.')
            ->expectsOutput('    Field #2:')
            ->expectsOutput('      x The type field is required.')
            ->expectsOutput('      x The name field is required.')
            ->expectsOutputToContain('All done')
            ->assertExitCode(1);
    }

    public function testWithMultiplePublicationTypes()
    {
        $this->directory('test-publication-1');
        $publicationType = new PublicationType('test-publication-1', fields: [
            ['name' => 'myField', 'type' => 'string'],
        ]);
        $publicationType->save();

        $this->directory('test-publication-2');
        $this->file('test-publication-2/schema.json', <<<'JSON'
            {
                "name": 123,
                "canonicalField": 123,
                "detailTemplate": 123,
                "listTemplate": 123,
                "sortField": 123,
                "sortAscending": 123,
                "pageSize": "123",
                 "fields": [
                    {
                        "name": 123,
                        "type": 123
                    },
                    {
                        "noName": "myField",
                        "noType": "string"
                    }
                ],
                "directory": "foo"
            }
            JSON
        );

        $this->artisan('validate:publicationTypes')
            ->expectsOutputToContain('Validating publication schemas!')
            ->expectsOutput('Validating schema file for [test-publication-1]')
            ->expectsOutput('  No top-level schema errors found')
            ->expectsOutput('  No field-level schema errors found')
            ->expectsOutput('Validating schema file for [test-publication-2]')
            ->expectsOutput('  Found 7 top-level schema errors:')
            ->expectsOutput('    x The name must be a string.')
            ->expectsOutput('    x The canonical field must be a string.')
            ->expectsOutput('    x The detail template must be a string.')
            ->expectsOutput('    x The list template must be a string.')
            ->expectsOutput('    x The sort field must be a string.')
            ->expectsOutput('    x The sort ascending field must be true or false.')
            ->expectsOutput('    x The directory field is prohibited.')
            ->expectsOutput('  Found errors in 2 field definitions:')
            ->expectsOutput('    Field #1:')
            ->expectsOutput('      x The type must be a string.')
            ->expectsOutput('      x The name must be a string.')
            ->expectsOutput('    Field #2:')
            ->expectsOutput('      x The type field is required.')
            ->expectsOutput('      x The name field is required.')
            ->expectsOutputToContain('All done')
            ->assertExitCode(1);
    }

    public function testWithNoFields()
    {
        $this->directory('test-publication');
        $publicationType = new PublicationType('test-publication');
        $publicationType->save();

        $this->artisan('validate:publicationTypes')
            ->expectsOutputToContain('Validating publication schemas!')
            ->expectsOutput('Validating schema file for [test-publication]')
            ->expectsOutput('  No top-level schema errors found')
            ->expectsOutput('  No field-level schema errors found')
            ->expectsOutputToContain('All done')
            ->assertExitCode(0);
    }

    public function testJsonOutputOption()
    {
        $this->directory('test-publication');
        $publicationType = new PublicationType('test-publication');
        $publicationType->save();

        $this->artisan('validate:publicationTypes --json')
            ->expectsOutput(<<<'JSON'
                {
                    "test-publication": {
                        "schema": [],
                        "fields": []
                    }
                }
                JSON)
            ->assertExitCode(0);
    }

    public function testMultipleTypesWithJsonOutput()
    {
        $this->directory('test-publication-1');
        $publicationType = new PublicationType('test-publication-1', fields: [
            ['name' => 'myField', 'type' => 'string'],
        ]);
        $publicationType->save();

        $this->directory('test-publication-2');
        $this->file('test-publication-2/schema.json', <<<'JSON'
            {
                "name": 123,
                "canonicalField": 123,
                "detailTemplate": 123,
                "listTemplate": 123,
                "sortField": 123,
                "sortAscending": 123,
                "pageSize": "123",
                 "fields": [
                    {
                        "name": 123,
                        "type": 123
                    },
                    {
                        "noName": "myField",
                        "noType": "string"
                    }
                ],
                "directory": "foo"
            }
            JSON
        );

        $this->artisan('validate:publicationTypes --json')
            ->expectsOutput(
                <<<'JSON'
                {
                    "test-publication-1": {
                        "schema": [],
                        "fields": [
                            []
                        ]
                    },
                    "test-publication-2": {
                        "schema": [
                            "The name must be a string.",
                            "The canonical field must be a string.",
                            "The detail template must be a string.",
                            "The list template must be a string.",
                            "The sort field must be a string.",
                            "The sort ascending field must be true or false.",
                            "The directory field is prohibited."
                        ],
                        "fields": [
                            [
                                "The type must be a string.",
                                "The name must be a string."
                            ],
                            [
                                "The type field is required.",
                                "The name field is required."
                            ]
                        ]
                    }
                }
                JSON
            )->assertExitCode(1);
    }
}
