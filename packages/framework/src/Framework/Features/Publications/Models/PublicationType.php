<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Publications\Models;

use function dirname;
use Exception;
use function file_get_contents;
use Hyde\Framework\Concerns\InteractsWithDirectories;
use Hyde\Hyde;
use Hyde\Support\Concerns\Serializable;
use Hyde\Support\Contracts\SerializableContract;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use function json_decode;
use RuntimeException;

/**
 * @see \Hyde\Framework\Testing\Feature\PublicationTypeTest
 */
class PublicationType implements SerializableContract
{
    use Serializable;
    use InteractsWithDirectories;

    protected string $directory;

    /** @var array<array<string, mixed>> */
    public array $fields = [];

    public static function get(string $name): static
    {
        return static::fromFile("$name/schema.json");
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

    public function __construct(
        public string $name,
        public string $canonicalField = 'identifier',
        public string $sortField = '__createdAt',
        public string $sortDirection = 'DESC',
        public int $pageSize = 25,
        public bool $prevNextLinks = true,
        public string $detailTemplate = 'detail',
        public string $listTemplate = 'list',
        array $fields = [],
        ?string $directory = null
    ) {
        $this->fields = $fields;
        $this->directory = $directory ?? Str::slug($name);
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'canonicalField' => $this->canonicalField,
            'sortField' => $this->sortField,
            'sortDirection' => $this->sortDirection,
            'pageSize' => $this->pageSize,
            'prevNextLinks' => $this->prevNextLinks,
            'detailTemplate' => $this->detailTemplate,
            'listTemplate' => $this->listTemplate,
            'fields' => $this->fields,
        ];
    }

    public function toJson($options = JSON_PRETTY_PRINT): string
    {
        return json_encode($this->toArray(), $options);
    }

    public function getIdentifier(): string
    {
        return $this->directory ?? Str::slug($this->name);
    }

    public function getSchemaFile(): string
    {
        return "$this->directory/schema.json";
    }

    public function getDirectory(): string
    {
        return $this->directory;
    }

    /** @return \Illuminate\Support\Collection<string, \Hyde\Framework\Features\Publications\Models\PublicationFieldType> */
    public function getFields(): Collection
    {
        return collect($this->fields)->mapWithKeys(function (array $data) {
            return [$data['name'] => new PublicationFieldType(...$data)];
        });
    }

    public function save(?string $path = null): void
    {
        $path ??= $this->getSchemaFile();
        $this->needsParentDirectory($path);
        file_put_contents(Hyde::path($path), json_encode($this->toArray(), JSON_PRETTY_PRINT));
    }

    public function getListPage(): PublicationListPage
    {
        return new PublicationListPage($this);
    }

    protected static function parseSchemaFile(string $schemaFile): array
    {
        return json_decode(file_get_contents(Hyde::path($schemaFile)), true, 512, JSON_THROW_ON_ERROR);
    }

    protected static function getRelativeDirectoryName(string $schemaFile): array
    {
        return ['directory' => Hyde::pathToRelative(dirname($schemaFile))];
    }
}
