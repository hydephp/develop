<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Publications\Models;

class PublicationType
{
    protected string $schemaFile;
    protected string $directory;
    protected array $schema;

    public function __construct(string $schemaFile)
    {
        $this->schemaFile = $schemaFile;
        $this->directory = dirname($schemaFile);
        $this->schema = static::parseSchema($schemaFile);
    }

    public function __get(string $name): mixed
    {
        return $this->$name ?? $this->schema[$name] ?? null;
    }

    protected static function parseSchema(string $schemaFile): array
    {
        return json_decode(file_get_contents($schemaFile), true, 512, JSON_THROW_ON_ERROR);
    }
}
