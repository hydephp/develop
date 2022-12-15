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
use Illuminate\Support\Str;
use function json_decode;
use Rgasch\Collection\Collection;
use RuntimeException;

/**
 * @see \Hyde\Framework\Testing\Feature\PublicationTypeTest
 */
class PublicationType implements SerializableContract
{
    use Serializable;
    use InteractsWithDirectories;

    public PaginationSettings|array $pagination = [];
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
                static::getRelativeDirectoryEntry($schemaFile))
            );
        } catch (Exception $exception) {
            throw new RuntimeException("Could not parse schema file $schemaFile", 0, $exception);
        }
    }

    public function __construct(
        public string $name,
        public string $canonicalField = 'identifier',
        public string $detailTemplate = 'detail',
        public string $listTemplate = 'list',
        array|PaginationSettings $pagination = [],
        array $fields = [],
        ?string $directory = null
    ) {
        $this->fields = $fields;
        $this->directory = $directory ?? Str::slug($name);
        $this->pagination = $pagination instanceof PaginationSettings
            ? $pagination
            : PaginationSettings::fromArray($pagination);
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'canonicalField' => $this->canonicalField,
            'detailTemplate' => $this->detailTemplate,
            'listTemplate' => $this->listTemplate,
            'pagination' => $this->pagination->toArray(),
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

    /** @return \Rgasch\Collection\Collection<string, \Hyde\Framework\Features\Publications\Models\PublicationFieldType> */
    public function getFields(): Collection
    {
        $result = collect($this->fields)->mapWithKeys(function (array $data): array {
            return [$data['name'] => new PublicationFieldType(...$data)];
        });

        return Collection::create($result, false);
    }

    /**
     * @param  bool  $reload
     * @return \Rgasch\Collection\Collection<string, \Rgasch\Collection\Collection>
     */
    public function getFieldRules(bool $reload = false): Collection
    {
        return Collection::create(
            $this->getFields()->mapWithKeys(function (PublicationFieldType $field) use ($reload) {
                return [$field->name => $field->getValidationRules($reload)];
            }), false);
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

    protected static function getRelativeDirectoryEntry(string $schemaFile): array
    {
        return ['directory' => Hyde::pathToRelative(dirname($schemaFile))];
    }
}
