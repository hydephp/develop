<?php

declare(strict_types=1);

namespace Hyde\Framework\Exceptions;

use InvalidArgumentException;

class InvalidConfigurationException extends InvalidArgumentException
{
    public function __construct(string $message = 'Invalid configuration detected.', ?string $namespace = null, ?string $key = null)
    {
        if ($namespace && $key) {
            [$file, $line] = $this->findConfigLine($namespace, $key);

            $this->file = $file;
            $this->line = $line;
        }

        parent::__construct($message);
    }

    /** @return array{string|null, int|null} */
    protected function findConfigLine(string $namespace, string $key): array
    {
        $file = "config/$namespace.php";
        $contents = file_get_contents(base_path($file));
        $lines = explode("\n", $contents);

        foreach ($lines as $line => $content) {
            if (str_contains($content, "'$key' =>")) {
                break;
            }
        }

        return [$file, $line + 1];
    }
}
