<?php

declare(strict_types=1);

namespace Hyde\Console\Commands;

use Exception;
use Hyde\Framework\Actions\CreatesNewMarkdownPostFile;
use LaravelZero\Framework\Commands\Command;
use function ucwords;

/**
 * Hyde Command to scaffold a new Markdown Post.
 */
class MakePostCommand extends Command
{
    /** @var string */
    protected $signature = 'make:post
                            {title? : The title for the Post. Will also be used to generate the filename}
                            {--force : Should the generated file overwrite existing posts with the same filename?}';

    /** @var string */
    protected $description = 'Scaffold a new Markdown blog post file';

    public function handle(): int
    {
        $this->title('Creating a new post!');

        $title = $this->getTitle();

        $this->line('Tip: You can just hit return to use the defaults.');
        $description = $this->ask('Write a short post excerpt/description');
        $author = $this->ask('What is your (the author\'s) name?');
        $category = $this->ask('What is the primary category of the post?');

        $this->info('Creating a post with the following details:');
        $creator = new CreatesNewMarkdownPostFile(
            $title,
            $description,
            $category,
            $author
        );

        $this->displaySelections($creator);

        if (! $this->confirm('Do you wish to continue?', true)) {
            $this->info('Aborting.');

            return 130;
        }

        try {
            $path = $creator->save($this->option('force'));
            $this->info("Post created! File is saved to $path");

            return Command::SUCCESS;
        } catch (Exception $exception) {
            $this->error('Something went wrong when trying to save the file!');
            $this->warn($exception->getMessage());
            if ($exception->getCode() === 409) {
                $this->comment('If you want to overwrite the file supply the --force flag.');
            }

            return (int) $exception->getCode();
        }
    }

    protected function getTitle(): mixed
    {
        $this->line($this->argument('title')
                ? '<info>Selected title: ' . $this->argument('title') . "</info>\n"
                : 'Please enter the title of the post, it will be used to generate the filename.'
        );

        return $this->argument('title')
            ?? $this->ask('What is the title of the post?')
            ?? 'My New Post';
    }

    protected function displaySelections(CreatesNewMarkdownPostFile $creator): void
    {
        foreach ($creator->toArray() as $key => $value) {
            $this->line(sprintf('%s: %s', ucwords($key), $value));
        }
        $this->line("Identifier: {$creator->getIdentifier()}");
    }
}
