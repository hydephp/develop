<?php

declare(strict_types=1);

class HydeStan
{
    protected array $errors = [];

    public function run(): void
    {
        //
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}
