<?php

namespace Hyde\Framework\Concerns\FrontMatter\Schemas\Constructors;

trait ConstructsPageSchemas
{
    protected function constructPageSchemas(): void
    {
        //

    protected function usesSchema(string $schema): bool
    {
        return in_array($schema, class_uses_recursive($this));
    }
}
