<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Publications\Models;

use Hyde\Hyde;

/**
 * @see \Hyde\Framework\Testing\Feature\PublicationTypeTest
 */
class PublicationType
{
    protected string $schemaFile;
    protected string $directory;

    public string $name;
    public string $canonicalField;
    public string $sortField;
    public string $sortDirection;
    public int $pagesize;
    public bool $prevNextLinks;
    public string $detailTemplate;
    public string $listTemplate;
    public array $fields;

    public function __construct(
        string $name,
        string $canonicalField,
        string $sortField,
        string $sortDirection,
        int $pagesize,
        bool $prevNextLinks,
        string $detailTemplate,
        string $listTemplate,
        array $fields,
        ?string $schemaFile = null,
        ?string $directory = null,
    ) {
        $this->name = $name;
        $this->canonicalField = $canonicalField;
        $this->sortField = $sortField;
        $this->sortDirection = $sortDirection;
        $this->pagesize = $pagesize;
        $this->prevNextLinks = $prevNextLinks;
        $this->detailTemplate = $detailTemplate;
        $this->listTemplate = $listTemplate;
        $this->fields = $fields;
        if ($schemaFile) {
            $this->schemaFile = $schemaFile;
        }
        if ($directory) {
            $this->directory = $directory;
        }
    }

    public static function get(string $name): self
    {
        return self::fromFile(Hyde::path("$name/schema.json"));
    }

    public static function fromFile(string $schemaFile): self
    {
        $directory = Hyde::pathToRelative(dirname($schemaFile));
        $schema = static::parseSchema($schemaFile);
        return new self(
            $schema['name'],
            $schema['canonicalField'],
            $schema['sortField'],
            $schema['sortDirection'],
            $schema['pagesize'],
            $schema['prevNextLinks'],
            $schema['detailTemplate'],
            $schema['listTemplate'],
            $schema['fields'],
            $schemaFile,
            $directory,
        );
    }

    public function getSchemaFile(): string
    {
        return $this->schemaFile;
    }

    public function getDirectory(): string
    {
        return $this->directory;
    }

    protected static function parseSchema(string $schemaFile): array
    {
        return json_decode(file_get_contents($schemaFile), true, 512, JSON_THROW_ON_ERROR);
    }
}
