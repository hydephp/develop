<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Publications\Models;

use Hyde\Framework\Concerns\InteractsWithDirectories;
use function dirname;
use function file_get_contents;
use Hyde\Hyde;
use Hyde\Support\Concerns\JsonSerializesArrayable;
use Illuminate\Contracts\Support\Arrayable;
use function json_decode;
use JsonSerializable;

/**
 * @see \Hyde\Framework\Testing\Feature\PublicationTypeTest
 */
class PublicationType implements JsonSerializable, Arrayable
{
    use JsonSerializesArrayable;
    use InteractsWithDirectories;

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

    public static function get(string $name): self
    {
        return self::fromFile(Hyde::path("$name/schema.json"));
    }

    public static function fromFile(string $schemaFile): self
    {
        $directory = Hyde::pathToRelative(dirname($schemaFile));
        $schema = static::parseSchema($schemaFile);

        return new self($schema['name'], $schema['canonicalField'], $schema['sortField'], $schema['sortDirection'], $schema['pagesize'], $schema['prevNextLinks'], $schema['detailTemplate'], $schema['listTemplate'], $schema['fields'], $directory);
    }

    public function __construct(string $name, string $canonicalField, string $sortField, string $sortDirection, int $pagesize, bool $prevNextLinks, string $detailTemplate, string $listTemplate, array $fields, ?string $directory = null)
    {
        $this->name = $name;
        $this->canonicalField = $canonicalField;
        $this->sortField = $sortField;
        $this->sortDirection = $sortDirection;
        $this->pagesize = $pagesize;
        $this->prevNextLinks = $prevNextLinks;
        $this->detailTemplate = $detailTemplate;
        $this->listTemplate = $listTemplate;
        $this->fields = $fields;

        if ($directory) {
            $this->directory = $directory;
        }
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'canonicalField' => $this->canonicalField,
            'sortField' => $this->sortField,
            'sortDirection' => $this->sortDirection,
            'pagesize' => $this->pagesize,
            'prevNextLinks' => $this->prevNextLinks,
            'detailTemplate' => $this->detailTemplate,
            'listTemplate' => $this->listTemplate,
            'fields' => $this->fields,
        ];
    }

    public function getSchemaFile(): string
    {
        return "$this->directory/schema.json";
    }

    public function getDirectory(): string
    {
        return $this->directory;
    }

    public function save(?string $path = null): void
    {
        $path ??= $this->getSchemaFile();
        $this->needsParentDirectory($path);
        file_put_contents($path, json_encode($this->toArray(), JSON_PRETTY_PRINT));
    }

    protected static function parseSchema(string $schemaFile): array
    {
        return json_decode(file_get_contents($schemaFile), true, 512, JSON_THROW_ON_ERROR);
    }
}
