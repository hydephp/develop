<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Publications\Models;

use function dirname;
use Exception;
use function file_get_contents;
use Hyde\Framework\Concerns\InteractsWithDirectories;
use Hyde\Hyde;
use Hyde\Support\Concerns\JsonSerializesArrayable;
use Illuminate\Contracts\Support\Arrayable;
use function json_decode;
use JsonSerializable;
use RuntimeException;

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

    public static function get(string $name): static
    {
        return static::fromFile(Hyde::path("$name/schema.json"));
    }

    public static function fromFile(string $schemaFile): static
    {
        try {
            return new static(...array_merge(
                static::parseSchemaFile($schemaFile),
                static::getRelativeDirectoryName($schemaFile))
            );
        } catch (Exception $exception) {
            throw new RuntimeException("Could not parse schema file $schemaFile", 0, $exception);
        }
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

    protected static function parseSchemaFile(string $schemaFile): array
    {
        return json_decode(file_get_contents($schemaFile), true, 512, JSON_THROW_ON_ERROR);
    }

    protected static function getRelativeDirectoryName(string $schemaFile): array
    {
        return ['directory' => Hyde::pathToRelative(dirname($schemaFile))];
    }
}
