<?php

declare(strict_types=1);

namespace Hyde\Framework\Exceptions;

use Hyde\Facades\Filesystem;
use InvalidArgumentException;

use function assert;
use function explode;
use function realpath;

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

    /** @return array{string, int} */
    protected function findConfigLine(string $namespace, string $key): array
    {
        $file = realpath("config/$namespace.php");
        $contents = Filesystem::getContents($file);
        $lines = explode("\n", $contents);

        foreach ($lines as $line => $content) {
            if (str_contains($content, "'$key' =>")) {
                break;
            }
        }

        assert($file !== false);
        assert(isset($line));

        return [$file, $line + 1];
    }
}
