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
    /** @deprecated */
    protected array $schema;

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
        return new self(Hyde::path("$name/schema.json"));
    }

    public static function fromFile(string $schemaFile): self
    {
        return new self($schemaFile);
    }


    public function __get(string $name): mixed
    {
        return $this->$name ?? $this->schema[$name] ?? null;
    }

    public function getSchemaFile(): string
    {
        return $this->schemaFile;
    }

    public function getDirectory(): string
    {
        return $this->directory;
    }

    public function getSchema(): array
    {
        return $this->schema;
    }

    protected static function parseSchema(string $schemaFile): array
    {
        return json_decode(file_get_contents($schemaFile), true, 512, JSON_THROW_ON_ERROR);
    }

    // TODO build list pages and detail pages for each publication type
}
