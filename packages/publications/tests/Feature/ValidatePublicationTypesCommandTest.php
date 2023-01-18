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
    public function testWithNoPublicationTypes()
    {
        $this->artisan('validate:publicationTypes')
            ->expectsOutput('Error: No publication types to validate!')
            ->assertExitCode(1);
    }

    public function testWithValidSchemaFile()
    {
        $this->directory('test-publication');
        $publicationType = new PublicationType('test-publication', fields: [
            ['name' => 'myField', 'type' => 'string'],
        ]);
        $publicationType->save();

        $this->artisan('validate:publicationTypes')
            ->assertExitCode(0);
    }


    public function testWithInvalidSchemaFile()
    {
        config(['app.throw_on_console_exception' => true]);

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
            ->assertExitCode(1);
    }
}
