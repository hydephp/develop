<?php

declare(strict_types=1);

namespace Hyde\Console\Commands;

use Hyde\Framework\Actions\CreatesNewPageSourceFile;
use Hyde\Framework\Exceptions\UnsupportedPageTypeException;
use Hyde\Pages\BladePage;
use Hyde\Pages\DocumentationPage;
use Hyde\Pages\MarkdownPage;
use LaravelZero\Framework\Commands\Command;
use function filled;
use function strtolower;
use function ucfirst;

/**
 * Hyde Command to scaffold a new Markdown or Blade page file.
 *
 * @see \Hyde\Framework\Testing\Feature\Commands\MakePageCommandTest
 */
class MakePageCommand extends Command
{
    /** @var string */
    protected $signature = 'make:page 
		{title? : The name of the page file to create. Will be used to generate the slug}
		{--type=markdown : The type of page to create (markdown, blade, or docs)}
        {--blade : Create a Blade page}
        {--docs : Create a Documentation page}
		{--force : Overwrite any existing files}';

    /** @var string */
    protected $description = 'Scaffold a new Markdown, Blade, or documentation page file';

    /**
     * The page title.
     */
    protected string $title;

    /**
     * The selected page type.
     */
    protected string $selectedType;

    /**
     * The page class type.
     *
     * @var class-string<\Hyde\Pages\Concerns\HydePage>
     */
    protected string $pageClass;

    /**
     * Can the file be overwritten?
     */
    protected bool $force;

    public function handle(): int
    {
        $this->title('Creating a new page!');

        $this->validateOptions();

        $this->line('<info>Creating a new '.ucfirst($this->selectedType).' page with title:</info> '.$this->title."\n");

        $this->force = $this->option('force') ?? false;

        $creator = new CreatesNewPageSourceFile($this->title, $this->pageClass, $this->force);

        $this->info("Created file {$creator->getOutputPath()}");

        return Command::SUCCESS;
    }

    protected function validateOptions(): void
    {
        $this->title = $this->argument('title')
            ?? $this->ask('What is the title of the page?')
            ?? 'My New Page';

        $this->selectedType = $this->getSelectedType();
        $this->pageClass = $this->getQualifiedPageType();
    }

    protected function getQualifiedPageType(): string
    {
        return match ($this->selectedType) {
            'blade' => BladePage::class,
            'markdown' => MarkdownPage::class,
            'docs', 'documentation' => DocumentationPage::class,
            default => throw new UnsupportedPageTypeException($this->selectedType),
        };
    }

    protected function getSelectedType(): string
    {
        return $this->getTypeOption() ?? $this->getTypeSelection();
    }

    protected function getTypeSelection(): string
    {
        return strtolower($this->option('type'));
    }

    protected function getTypeOption(): ?string
    {
        if ($this->option('blade')) {
            return 'blade';
        }

        if ($this->option('docs')) {
            return 'documentation';
        }

        return null;
    }
}
