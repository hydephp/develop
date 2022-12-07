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
 * Hyde Command to create a new publication for a given publication type.
 *
 * @see \Hyde\Framework\Actions\CreatesNewPublicationFile
 * @see \Hyde\Framework\Testing\Feature\Commands\MakePublicationCommandTest
 */
class SeedPublicationCommand extends ValidatingCommand implements CommandHandleInterface
{
    /** @var string */
    protected $signature = 'seed:publication
		{publicationType? : The name of the PublicationType to create a publication for}
        {number? : The number of publications to generate}';

    /** @var string */
    protected $description = 'Generate random publications for a publication type';

    public function safeHandle(): int
    {
        $this->title('Seeding new Publication!');

        $pubType = $this->getPubTypeSelection($this->getPublicationTypes());
        $number  = (int) ($this->argument('number') ?? $this->askWithValidation(
            'number',
            'How many publications would you like to generate:',
            ['required', 'integer', 'between:1,100000']
        ));

        $seeder = new SeedsPublicationFiles($pubType, $number);
        $seeder->create();

        $this->info("$number publications for $pubType->name created!");

        return Command::SUCCESS;
    }

    /**
     * @param \Rgasch\Collection\Collection<string, \Hyde\Framework\Features\Publications\Models\PublicationType> $pubTypes
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
            $this->line("<info>Creating random publications of type</info> [<comment>$pubTypeSelection</comment>]");

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
