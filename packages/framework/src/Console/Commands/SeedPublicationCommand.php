<?php

declare(strict_types=1);

namespace Hyde\Console\Commands;

use Hyde\Console\Commands\Interfaces\CommandHandleInterface;
use Hyde\Console\Concerns\ValidatingCommand;
use Hyde\Framework\Actions\SeedsPublicationFiles;
use Hyde\Framework\Features\Publications\Models\PublicationType;
use Hyde\Framework\Features\Publications\PublicationService;
use InvalidArgumentException;
use LaravelZero\Framework\Commands\Command;
use Rgasch\Collection\Collection;

/**
 * Hyde Command to seed publication files for a publication type.
 *
 * @see \Hyde\Framework\Actions\SeedsPublicationFiles
 * @see \Hyde\Framework\Testing\Feature\Commands\SeedPublicationCommandTest
 *
 * @todo Normalize command output style, maybe by hooking into the build actions?
 */
class SeedPublicationCommand extends ValidatingCommand implements CommandHandleInterface
{
    /** @var string */
    protected $signature = 'seed:publications
		{publicationType? : The name of the publication type to create publications for}
        {number? : The number of publications to generate}';

    /** @var string */
    protected $description = 'Generate random publications for a publication type';

    public function safeHandle(): int
    {
        $this->title('Seeding new publications!');

        $pubType = $this->getPubTypeSelection($this->getPublicationTypes());
        $number = (int) ($this->argument('number') ?? $this->askWithValidation(
            'number',
            'How many publications would you like to generate',
            ['required', 'integer', 'between:1,100000'], 1));

        if ($number >= 10000) {
            $this->warn('Warning: Generating a large number of publications may take a while. <fg=gray>Expected time: ' . ($number / 1000) . ' seconds.</>');
            if (! $this->confirm('Are you sure you want to continue?')) {
                return parent::USER_EXIT;
            }
        }

        $timeStart = microtime(true);
        $seeder = new SeedsPublicationFiles($pubType, $number);
        $seeder->create();

        $ms = round((microtime(true) - $timeStart) * 1000);
        $each = round($ms / $number, 2);
        $this->info(sprintf("<comment>$number</comment> publications for <comment>$pubType->name</comment> created! <fg=gray>Took {$ms}ms%s",
                ($number > 1) ? " ({$each}ms/each)</>" : ''));

        return Command::SUCCESS;
    }

    /**
     * @param  \Rgasch\Collection\Collection<string, \Hyde\Framework\Features\Publications\Models\PublicationType>  $pubTypes
     * @return \Hyde\Framework\Features\Publications\Models\PublicationType
     */
    protected function getPubTypeSelection(Collection $pubTypes): PublicationType
    {
        $pubTypeSelection = $this->argument('publicationType') ?? $pubTypes->keys()->get(
            (int) $this->choice(
                'Which publication type would you like to generate publications for?',
                $pubTypes->keys()->toArray()
            )
        );

        if ($pubTypes->has($pubTypeSelection)) {
            if ($this->argument('number')) {
                $this->line("<info>Creating</info> [<comment>{$this->argument('number')}</comment>] <info>random publications for type</info> [<comment>$pubTypeSelection</comment>]");
            } else {
                $this->line("<info>Creating random publications for type</info> [<comment>$pubTypeSelection</comment>]");
            }

            return $pubTypes->get($pubTypeSelection);
        }

        throw new InvalidArgumentException("Unable to locate publication type [$pubTypeSelection]");
    }

    /**
     * @return \Rgasch\Collection\Collection<string, PublicationType>
     *
     * @throws \InvalidArgumentException
     */
    protected function getPublicationTypes(): Collection
    {
        $pubTypes = PublicationService::getPublicationTypes();
        if ($pubTypes->isEmpty()) {
            throw new InvalidArgumentException('Unable to locate any publication types. Did you create any?');
        }

        return $pubTypes;
    }
}
