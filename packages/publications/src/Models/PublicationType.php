<?php

declare(strict_types=1);

namespace Hyde\Publications\Models;

use Hyde\Facades\Filesystem;
use Illuminate\Contracts\Validation\Validator;
use function array_filter;
use function array_merge;
use function dirname;
use Exception;
use function file_get_contents;
use function file_put_contents;
use Hyde\Framework\Concerns\InteractsWithDirectories;
use Hyde\Framework\Features\Paginator;
use Hyde\Hyde;
use Hyde\Publications\PublicationService;
use Hyde\Support\Concerns\Serializable;
use Hyde\Support\Contracts\SerializableContract;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use function json_decode;
use function json_encode;
use RuntimeException;
use function str_starts_with;
use function validator;

/**
 * @see \Hyde\Publications\Testing\Feature\PublicationTypeTest
 */
class PublicationType implements SerializableContract
{
    use Serializable;
    use InteractsWithDirectories;

    /** The "pretty" name of the publication type */
    public string $name;

    /**
     * The field name that is used as the canonical (or identifying) field of publications.
     *
     * It's used primarily for generating filenames, and the publications must thus be unique by this field.
     */
    public string $canonicalField = '__createdAt';

    /** The Blade filename or view identifier used for rendering a single publication */
    public string $detailTemplate = 'detail.blade.php';

    /** The Blade filename or view identifier used for rendering the index page (or index pages, when using pagination) */
    public string $listTemplate = 'list.blade.php';

    /** The field that is used for sorting publications. */
    public string $sortField = '__createdAt';

    /** Whether the sort field should be sorted in ascending order. */
    public bool $sortAscending = true;

    /** The number of publications to show per paginated page. Set to 0 to disable pagination. */
    public int $pageSize = 0;

    /**
     * The front matter fields used for the publications.
     *
     * @var \Illuminate\Support\Collection<string, \Hyde\Publications\Models\PublicationFieldDefinition>
     */
    public Collection $fields;

    /** The directory of the publication files */
    protected string $directory;

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
        string $name, // todo get from directory name if not set in schema?
        string $canonicalField = '__createdAt',
        string $detailTemplate = 'detail.blade.php',
        string $listTemplate = 'list.blade.php',
        string $sortField = '__createdAt',
        bool $sortAscending = true,
        int $pageSize = 0,
        array $fields = [],
        ?string $directory = null
    ) {
        $this->name = $name; // todo get from directory name if not set in schema?
        $this->canonicalField = $canonicalField;
        $this->detailTemplate = $detailTemplate;
        $this->listTemplate = $listTemplate;
        $this->fields = $this->parseFieldData($fields);
        $this->directory = $directory ?? Str::slug($name);
        $this->sortField = $sortField;
        $this->sortAscending = $sortAscending;
        $this->pageSize = $pageSize;
    }

    public function toArray(): array
    {
        return $this->withoutNullValues([
            'name' => $this->name,
            'canonicalField' => $this->canonicalField,
            'detailTemplate' => $this->detailTemplate,
            'listTemplate' => $this->listTemplate,
            'sortField' => $this->sortField,
            'sortAscending' => $this->sortAscending,
            'pageSize' => $this->pageSize,
            'fields' => $this->fields->toArray(),
        ]);
    }

    public function toJson($options = JSON_PRETTY_PRINT): string
    {
        return json_encode($this->toArray(), $options);
    }

    /** Get the publication type's identifier */
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

    /**
     * Get the publication fields, deserialized to PublicationFieldDefinition objects.
     *
     * @return \Illuminate\Support\Collection<string, \Hyde\Publications\Models\PublicationFieldDefinition>
     */
    public function getFields(): Collection
    {
        return $this->fields;
    }

    public function getFieldDefinition(string $fieldName): PublicationFieldDefinition
    {
        return $this->getFields()->filter(fn (PublicationFieldDefinition $field): bool => $field->name === $fieldName)->firstOrFail();
    }

    public function getCanonicalFieldDefinition(): PublicationFieldDefinition
    {
        if (str_starts_with($this->canonicalField, '__')) {
            return new PublicationFieldDefinition('string', $this->canonicalField);
        }

        return $this->getFields()->filter(fn (PublicationFieldDefinition $field): bool => $field->name === $this->canonicalField)->first();
    }

    /** @return \Illuminate\Support\Collection<\Hyde\Publications\Models\PublicationPage> */
    public function getPublications(): Collection
    {
        return PublicationService::getPublicationsForPubType($this);
    }

    public function getPaginator(int $currentPageNumber = null): Paginator
    {
        return new Paginator($this->getPublications(),
            $this->pageSize,
            $currentPageNumber,
            $this->getIdentifier()
        );
    }

    public function getListPage(): PublicationListPage
    {
        return new PublicationListPage($this);
    }

    public function usesPagination(): bool
    {
        return ($this->pageSize > 0) && ($this->pageSize < $this->getPublications()->count());
    }

    public function save(?string $path = null): void
    {
        $path ??= $this->getSchemaFile();
        $this->needsParentDirectory($path);
        file_put_contents(Hyde::path($path), json_encode($this->toArray(), JSON_PRETTY_PRINT));
    }

    protected static function parseSchemaFile(string $schemaFile): array
    {
        return json_decode(file_get_contents(Hyde::path($schemaFile)), true, 512, JSON_THROW_ON_ERROR);
    }

    protected static function getRelativeDirectoryEntry(string $schemaFile): array
    {
        return ['directory' => Hyde::pathToRelative(dirname($schemaFile))];
    }

    protected function parseFieldData(array $fields): Collection
    {
        return Collection::make($fields)->map(function (array $data): PublicationFieldDefinition {
            return new PublicationFieldDefinition(...$data);
        });
    }

    protected function withoutNullValues(array $array): array
    {
        return array_filter($array, fn (mixed $value): bool => ! is_null($value));
    }

    /**
     * Validate the schema.json file is valid.
     *
     * @internal This method is experimental and may be removed without notice
     */
    public function validateSchemaFile(bool $throw = true): array
    {
        $schema = json_decode(Filesystem::getContents($this->getSchemaFile()));
        $errors = [];

        $schemaValidator = validator([
            'name' => $schema->name ?? null,
            'canonicalField' => $schema->canonicalField ?? null,
            'detailTemplate' => $schema->detailTemplate ?? null,
            'listTemplate' => $schema->listTemplate ?? null,
            'sortField' => $schema->sortField ?? null,
            'sortAscending' => $schema->sortAscending ?? null,
            'pageSize' => $schema->pageSize ?? null,
            'fields' => $schema->fields ?? null,
            'directory' => $schema->directory ?? null,
        ], [
            'name' => 'required|string',
            'canonicalField' => 'nullable|string',
            'detailTemplate' => 'nullable|string',
            'listTemplate' => 'nullable|string',
            'sortField' => 'nullable|string',
            'sortAscending' => 'nullable|boolean',
            'pageSize' => 'nullable|integer',
            'fields' => 'nullable|array',
            'directory' => 'nullable|prohibited',
        ]);

        $errors['schema'] = $schemaValidator->errors()->toArray();

        if ($throw) {
            $schemaValidator->validate();
        }

        // TODO warn if fields are empty?

        // TODO warn if canonicalField does not match meta field or actual?

        // TODO Warn if template files do not exist (assuming files not vendor views)?

        // TODO warn if pageSize is less than 0 (as that equals no pagination)?

        $errors['fields'] = [];

        foreach ($schema->fields as $field) {
            $fieldValidator = validator([
                'type' => $field->type ?? null,
                'name' => $field->name ?? null,
                'rules' => $field->rules ?? null,
                'tagGroup' => $field->tagGroup ?? null,
            ], [
                'type' => 'required|string',
                'name' => 'required|string',
                'rules' => 'nullable|array',
                'tagGroup' => 'nullable|string',
            ]);

            // TODO check tag group exists?

            $errors['fields'][] = $fieldValidator->errors()->toArray();

            if ($throw) {
                $fieldValidator->validate();
            }
        }

        return $errors;
    }
}
