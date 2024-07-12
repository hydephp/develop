<?php

declare(strict_types=1);

namespace Hyde\MonorepoDevTools;

use Hyde\Hyde;
use Throwable;
use Hyde\Enums\Feature;
use Illuminate\Support\Str;
use Symfony\Component\Yaml\Yaml;
use Hyde\Console\Concerns\Command;
use Hyde\Framework\Features\Blogging\Models\PostAuthor;
use Hyde\Framework\Features\Metadata\MetadataElementContract;

use function copy;
use function config;
use function substr;
use function unlink;
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
 */
class RefactorConfigCommand extends Command
{
    /** @var string */
    protected $signature = 'refactor:config {format : The new configuration format}';

    /** @var string */
    protected $description = 'Migrate the configuration to a different format.';

    protected const FORMATS = ['yaml'];

    public function handle(): int
    {
        $format = $this->argument('format');
        if (! in_array($format, self::FORMATS)) {
            $this->error('Invalid format. Supported formats: '.implode(', ', self::FORMATS));

            return 1;
        }

        $this->gray(' > Migrating configuration to '.$format);

        match ($format) {
            'yaml' => $this->migrateToYaml(),
        };

        $this->info('All done!');

        return 0;
    }

    protected function migrateToYaml(): void
    {
        $usesGit = file_exists(Hyde::path('.git'));

        if (file_exists(Hyde::path('hyde.yml')) && ! file_exists(Hyde::path('hyde.yml.bak'))) {
            copy(Hyde::path('hyde.yml'), Hyde::path('hyde.yml.bak'));
            if (! $usesGit) {
                $this->warn("You're not using Git for version control, so a backup of your configuration has been created at hyde.yml.bak.");
            }
        }

        try {
            $config = config('hyde');
            $config = $this->serializePhpData($config);

            $yaml = Yaml::dump($config, 16, 4, Yaml::DUMP_MULTI_LINE_LITERAL_BLOCK | Yaml::DUMP_EMPTY_ARRAY_AS_SEQUENCE);

            // Todo: Diff out defaults? (unless with argument)

            file_put_contents(Hyde::path('hyde.yml'), $yaml);
        } catch (Throwable $exception) {
            $this->error('Failed to migrate configuration: '.$exception->getMessage());
            $this->warn('Rolling back changes...');
            copy(Hyde::path('hyde.yml.bak'), Hyde::path('hyde.yml'));
            unlink(Hyde::path('hyde.yml.bak'));

            throw $exception;
        } finally {
            if ($usesGit && file_exists(Hyde::path('hyde.yml.bak'))) {
                unlink(Hyde::path('hyde.yml.bak'));
            }
        }
    }

    protected function serializePhpData(array $config): array
    {
        return collect($config)->mapWithKeys(function ($value, $key) {
            if (is_array($value)) {
                return [$key => $this->serializePhpData($value)];
            }

            return $this->serializePhpValue($value, $key);
        })->toArray();
    }

    protected function serializePhpValue(mixed $value, string|int $key): array
    {
        if ($value instanceof Feature) {
            return [$key => Str::kebab($value->name)];
        }

        if (is_string($key) && str_starts_with($key, 'Hyde\Pages\\')) {
            return [Str::kebab(substr($key, 11)) => $value];
        }

        if ($value instanceof MetadataElementContract) {
            // We don't have deserialization logic for this (yet?)
            return [$key => $value->__toString()];
        }

        if ($value instanceof PostAuthor) {
            // Not fully supported in v1
            return [$key => [
                'username' => $value->username,
                'name' => $value->name,
                'website' => $value->website,
            ]];
        }

        return [$key => $value];
    }
}
