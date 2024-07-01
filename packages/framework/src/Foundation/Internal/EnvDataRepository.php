<?php

declare(strict_types=1);

namespace Hyde\Foundation\Internal;

use Illuminate\Support\Env;

/**
 * @internal Contains dynamic environment data.
 */
class EnvDataRepository
{
    protected array $data = [];

    public function __construct()
    {
        // Set the data we support by default.

        $this->data = [
            'SITE_NAME' => Env::get('SITE_NAME'),
        ];
    }

    public function set(string $key, mixed $value): void
    {
        $this->data[$key] = $value;
    }

    public function get(string $key): mixed
    {
        return $this->data[$key] ?? null;
    }
}
