<?php

declare(strict_types=1);

namespace Hyde\MonorepoDevTools;

use Hyde\Hyde;
use RuntimeException;
use Hyde\Enums\Feature;
use Illuminate\Support\Str;
use Symfony\Component\Yaml\Yaml;
use Hyde\Console\Concerns\Command;
use Hyde\Framework\Features\Blogging\Models\PostAuthor;
use Hyde\Framework\Features\Metadata\MetadataElementContract;

use function config;
use function substr;
use function collect;
use function implode;
use function in_array;
use function is_array;
use function is_string;
use function file_exists;
use function str_starts_with;
use function file_put_contents;

/**
 * @internal This class is internal to the hydephp/develop monorepo.
 *
 * @experimental https://github.com/hydephp/develop/pull/1833
 */
class RefactorConfigCommand extends Command
{
    protected const SUPPORTED_FORMATS = ['yaml'];

    /** @var string */
    protected $signature = 'refactor:config {format : The new configuration format}';

    /** @var string */
    protected $description = 'Migrate the configuration to a different format.';

    public function handle(): int
    {
        $format = $this->argument('format');
        if (! in_array($format, self::SUPPORTED_FORMATS)) {
            $this->error('Invalid format. Supported formats: '.implode(', ', self::SUPPORTED_FORMATS));

            return 1;
        }

        $this->gray(" > Migrating configuration to $format");

        return match ($format) {
            'yaml' => $this->migrateToYaml(),
        };
    }

    protected function migrateToYaml(): int
    {
        $this->ensureYamlConfigDoesNotExist();

        $config = $this->getConfigDiff();

        if (empty($config)) {
            $this->warn("You don't seem to have any configuration to migrate.");

            return 0;
        }

        $serializedConfig = $this->serializePhpData($config);
        $yaml = $this->dumpConfigToYaml($serializedConfig);

        file_put_contents(Hyde::path('hyde.yml'), $yaml);

        $this->info('All done!');

        return 0;
    }

    protected function ensureYamlConfigDoesNotExist(): void
    {
        if (file_exists(Hyde::path('hyde.yml')) || file_exists(Hyde::path('hyde.yaml'))) {
            throw new RuntimeException('Configuration already exists in YAML format.');
        }
    }

    protected function getConfigDiff(): array
    {
        $config = config('hyde');
        $default = require Hyde::vendorPath('config/hyde.php');

        return $this->diffConfig($config, $default);
    }

    protected function dumpConfigToYaml(array $config): string
    {
        return Yaml::dump($config, 16, 4, Yaml::DUMP_MULTI_LINE_LITERAL_BLOCK | Yaml::DUMP_EMPTY_ARRAY_AS_SEQUENCE);
    }

    /**
     * @param  array<string|int, mixed>  $config
     * @return array<string|int, mixed>
     */
    protected function serializePhpData(array $config): array
    {
        return collect($config)->mapWithKeys(function ($value, $key) {
            if (is_array($value)) {
                return [$key => $this->serializePhpData($value)];
            }

            return $this->serializePhpValue($value, $key);
        })->toArray();
    }

    /**
     * @param  mixed  $value
     * @param  string|int  $key
     * @return array<string|int, mixed>
     */
    protected function serializePhpValue(mixed $value, string|int $key): array
    {
        if ($value instanceof Feature) {
            return [$key => Str::kebab($value->name)];
        }

        if (is_string($key) && str_starts_with($key, 'Hyde\Pages\\')) {
            return [Str::kebab(substr($key, 11)) => $value];
        }

        if ($value instanceof MetadataElementContract) {
            return [$key => $value->__toString()];
        }

        if ($value instanceof PostAuthor) {
            return [$key => $this->serializePostAuthor($value)];
        }

        return [$key => $value];
    }

    protected function serializePostAuthor(PostAuthor $author): array
    {
        return [
            'username' => $author->username,
            'name' => $author->name,
            'website' => $author->website,
        ];
    }

    /**
     * @param  array<string, mixed>  $config
     * @param  array<string, mixed>  $default
     * @return array<string, mixed>
     */
    protected function diffConfig(array $config, array $default): array
    {
        $diff = [];

        foreach ($config as $key => $value) {
            if (! isset($default[$key]) || $value != $default[$key]) {
                $diff[$key] = $value;
            }
        }

        return $this->arrayFilterRecurse($diff);
    }

    /**
     * @param  array<string|int, mixed>  $input
     * @return array<string|int, mixed>
     */
    protected function arrayFilterRecurse(array $input): array
    {
        foreach ($input as $key => &$value) {
            if (is_array($value)) {
                $value = $this->arrayFilterRecurse($value);
                if (empty($value)) {
                    unset($input[$key]);
                }
            } elseif (blank($value)) {
                unset($input[$key]);
            }
        }

        return $input;
    }
}
