<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Publications\Models;

use Hyde\Hyde;

class PublicationType
{
    protected string $schemaFile;
    protected string $directory;
    protected array $schema;

    public static function get(string $name): self
    {
        return new self(Hyde::path("$name/schema.json"));
    }

    public function __construct(string $schemaFile)
    {
        $this->schemaFile = $schemaFile;
        $this->directory = Hyde::pathToRelative(dirname($schemaFile));
        $this->schema = static::parseSchema($schemaFile);
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
