<?php

declare(strict_types=1);

namespace Hyde\Publications\Testing\Feature;

use Hyde\Publications\Models\PublicationType;
use Hyde\Testing\TestCase;
use Illuminate\Validation\ValidationException;

/**
 * @covers \Hyde\Publications\Actions\ValidatesPublicationSchema
 */
class ValidatesPublicationSchemaTest extends TestCase
{
    public function testValidateSchemaFile()
    {
        $this->directory('test-publication');
        $publicationType = new PublicationType('test-publication', fields: [
            ['name' => 'myField', 'type' => 'string'],
        ]);
        $publicationType->save();

        $publicationType->validateSchemaFile();

        $this->assertTrue(true);
    }

    public function testValidateSchemaFileWithInvalidSchema()
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
                "fields": 123,
                "directory": "foo"
            }
            JSON
        );

        $this->expectException(ValidationException::class);
        $publicationType->validateSchemaFile();
    }

    public function testValidateSchemaFileWithInvalidFields()
    {
        $this->directory('test-publication');
        $publicationType = new PublicationType('test-publication');
        $publicationType->save();

        $this->file('test-publication/schema.json', <<<'JSON'
            {
                "name": "test-publication",
                "canonicalField": "__createdAt",
                "detailTemplate": "detail.blade.php",
                "listTemplate": "list.blade.php",
                "sortField": "__createdAt",
                "sortAscending": true,
                "pageSize": 0,
                "fields": [
                    {
                        "name": 123,
                        "type": 123
                    },
                    {
                        "noName": "myField",
                        "noType": "string"
                    }
                ]
            }
            JSON
        );

        $this->expectException(ValidationException::class);
        $publicationType->validateSchemaFile();
    }

    public function testValidateSchemaFileWithInvalidDataBuffered()
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

        $errors = $publicationType->validateSchemaFile(false);

        $this->assertSame([
            'schema' => [
                'name' => ['The name must be a string.'],
                'canonicalField' => ['The canonical field must be a string.'],
                'detailTemplate' => ['The detail template must be a string.'],
                'listTemplate' => ['The list template must be a string.'],
                'sortField' => ['The sort field must be a string.'],
                'sortAscending' => ['The sort ascending field must be true or false.'],
                'directory' => ['The directory field is prohibited.'],
            ],
            'fields' => [[
                'type' => ['The type must be a string.'],
                'name' => ['The name must be a string.'],
            ], [
                'type' => ['The type field is required.'],
                'name' => ['The name field is required.'],
            ]],
        ], $errors);
    }
}
