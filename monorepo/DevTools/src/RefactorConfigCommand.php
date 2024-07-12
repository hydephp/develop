<?php

declare(strict_types=1);

namespace Hyde\MonorepoDevTools;

use Hyde\Hyde;
use Symfony\Component\Yaml\Yaml;
use Hyde\Console\Concerns\Command;

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
        // todo if file exists, add backup

        $config = config('hyde');

        $yaml = Yaml::dump($config);

        // todo diff out defaults?

        file_put_contents(Hyde::path('hyde.yml'), $yaml);
    }
}
