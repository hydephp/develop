<?php

declare(strict_types=1);

namespace Hyde\Publications\Testing\Feature;

use function file_put_contents;
use Hyde\Framework\Exceptions\FileNotFoundException;
use Hyde\Hyde;
use Hyde\Publications\Models\PublicationPage;
use Hyde\Publications\Models\PublicationType;
use Hyde\Publications\PublicationService;
use Hyde\Testing\TestCase;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Validation\ValidationException;
use function json_encode;
use function mkdir;

/**
 * @covers \Hyde\Publications\PublicationService
 */
class PublicationServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        mkdir(Hyde::path('test-publication'));
    }

    protected function tearDown(): void
    {
        File::deleteDirectory(Hyde::path('test-publication'));

        parent::tearDown();
    }

    public function testGetPublicationTypes()
    {
        $this->assertEquals(new Collection(), PublicationService::getPublicationTypes());
    }

    public function testGetPublicationTypesWithTypes()
    {
        $this->createPublicationType();

        $this->assertEquals(new Collection([
            'test-publication' => PublicationType::get('test-publication'),
        ]), PublicationService::getPublicationTypes());
    }

    public function testGetPublicationsForPubType()
    {
        $this->createPublicationType();

        $this->assertEquals(
            new Collection(),
            PublicationService::getPublicationsForPubType(PublicationType::get('test-publication'))
        );
    }

    public function testGetPublicationsForPubTypeWithPublications()
    {
        $this->createPublicationType();
        $this->createPublication();

        $this->assertEquals(
            new Collection([
                PublicationService::parsePublicationFile('test-publication/foo.md'),
            ]),
            PublicationService::getPublicationsForPubType(PublicationType::get('test-publication'))
        );
    }

    public function testGetPublicationsForPubTypeOnlyContainsInstancesOfPublicationPage()
    {
        $this->createPublicationType();
        $this->createPublication();

        $this->assertContainsOnlyInstancesOf(
            PublicationPage::class,
            PublicationService::getPublicationsForPubType(PublicationType::get('test-publication'))
        );
    }

    public function testGetPublicationsForPubTypeSortsPublicationsBySortField()
    {
        (new PublicationType('test-publication', sortField: 'order'))->save();

        $this->markdown('test-publication/one.md', matter: ['order' => 1]);
        $this->markdown('test-publication/two.md', matter: ['order' => 2]);
        $this->markdown('test-publication/three.md', matter: ['order' => 3]);

        $this->assertEquals(
            new Collection([
                PublicationService::parsePublicationFile('test-publication/one.md'),
                PublicationService::parsePublicationFile('test-publication/two.md'),
                PublicationService::parsePublicationFile('test-publication/three.md'),
            ]),
            PublicationService::getPublicationsForPubType(PublicationType::get('test-publication'))
        );
    }

    public function testGetPublicationsForPubTypeSortsPublicationsWithSpecifiedDirection()
    {
        (new PublicationType('test-publication', sortField: 'order', sortAscending: false))->save();

        $this->markdown('test-publication/one.md', matter: ['order' => 1]);
        $this->markdown('test-publication/two.md', matter: ['order' => 2]);
        $this->markdown('test-publication/three.md', matter: ['order' => 3]);

        $this->assertEquals(
            new Collection([
                PublicationService::parsePublicationFile('test-publication/three.md'),
                PublicationService::parsePublicationFile('test-publication/two.md'),
                PublicationService::parsePublicationFile('test-publication/one.md'),
            ]),
            PublicationService::getPublicationsForPubType(PublicationType::get('test-publication'))
        );
    }

    public function testGetMediaForPubType()
    {
        $this->createPublicationType();

        $this->assertEquals(
            new Collection(),
            PublicationService::getMediaForPubType(PublicationType::get('test-publication'))
        );
    }

    public function testGetMediaForPubTypeWithMedia()
    {
        $this->createPublicationType();
        mkdir(Hyde::path('_media/test-publication'));
        file_put_contents(Hyde::path('_media/test-publication/image.png'), '');

        $this->assertEquals(
            new Collection([
                '_media/test-publication/image.png',
            ]),
            PublicationService::getMediaForPubType(PublicationType::get('test-publication'))
        );

        File::deleteDirectory(Hyde::path('_media/test-publication'));
    }

    public function testParsePublicationFile()
    {
        $this->createPublicationType();
        $this->createPublication();

        $file = PublicationService::parsePublicationFile('test-publication/foo');
        $this->assertInstanceOf(PublicationPage::class, $file);
        $this->assertEquals('test-publication/foo', $file->getIdentifier());
    }

    public function testParsePublicationFileWithFileExtension()
    {
        $this->createPublicationType();
        $this->createPublication();

        $this->assertEquals(
            PublicationService::parsePublicationFile('test-publication/foo'),
            PublicationService::parsePublicationFile('test-publication/foo.md')
        );
    }

    public function testParsePublicationFileWithNonExistentFile()
    {
        $this->createPublicationType();

        $this->expectException(FileNotFoundException::class);
        $this->expectExceptionMessage('File [test-publication/foo.md] not found.');

        PublicationService::parsePublicationFile('test-publication/foo');
    }

    public function testPublicationTypeExists()
    {
        $this->createPublicationType();

        $this->assertTrue(PublicationService::publicationTypeExists('test-publication'));
        $this->assertFalse(PublicationService::publicationTypeExists('foo'));
    }

    public function testGetAllTags()
    {
        $tags = [
            'foo' => [
                'bar',
                'baz',
            ],
        ];
        $this->file('tags.yml', json_encode($tags));
        $this->assertSame($tags, PublicationService::getAllTags()->toArray());
    }

    public function testGetValuesForTagName()
    {
        $tags = [
            'foo' => [
                'bar',
                'baz',
            ],
            'bar' => [
                'baz',
                'qux',
            ],
        ];

        $this->file('tags.yml', json_encode($tags));

        $this->assertSame(['bar', 'baz'], PublicationService::getValuesForTagName('foo')->toArray());
    }

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

    protected function createPublicationType(): void
    {
        (new PublicationType('test-publication'))->save();
    }

    protected function createPublication(): void
    {
        file_put_contents(
            Hyde::path('test-publication/foo.md'),
            "---\n__canonical: canonical\n__createdAt: 2022-11-16 11:32:52\nfoo: bar\n---\n\nHello World!\n"
        );
    }
}
