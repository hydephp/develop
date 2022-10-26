<?php

declare(strict_types=1);

namespace Hyde\Console\Commands;

use Exception;
use Hyde\Framework\Actions\CreatesNewMarkdownPostFile;
use LaravelZero\Framework\Commands\Command;

/**
 * Hyde Command to scaffold a new Markdown Post.
 */
class MakePostCommand extends Command
{
    /** @var string */
    protected $signature = 'make:post
                            {title? : The title for the Post. Will be used to generate the slug}
                            {--force : Should the generated file overwrite existing posts with the same slug?}';

    /** @var string */
    protected $description = 'Scaffold a new Markdown blog post file';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $this->title('Creating a new post!');

        $this->line(
            $this->argument('title')
                ? '<info>Selected title: '.$this->argument('title')."</info>\n"
                : 'Please enter the title of the post, it will be used to generate the slug.'
        );

        $title = $this->argument('title')
            ?? $this->ask('What is the title of the post?')
            ?? 'My New Post';

        $this->line('Tip: You can just hit return to use the defaults.');
        $description = $this->ask('Write a short post excerpt/description');
        $author = $this->ask('What is your (the author\'s) name?');
        $category = $this->ask('What is the primary category of the post?');

        $this->info('Creating a post with the following details:');
        $creator = new CreatesNewMarkdownPostFile(
            title: $title,
            description: $description,
            category: $category,
            author: $author
        );

        $this->line("Title: $creator->title");
        $this->line("Description: $creator->description");
        $this->line("Author: $creator->author");
        $this->line("Category: $creator->category");
        $this->line("Date: $creator->date");
        $this->line("Slug: $creator->slug");

        if (! $this->confirm('Do you wish to continue?', true)) {
            $this->info('Aborting.');

            return 130;
        }

        try {
            $path = $creator->save($this->option('force'));
            $this->info("Post created! File is saved to $path");

            return 0;
        } catch (Exception $exception) {
            $this->error('Something went wrong when trying to save the file!');
            $this->warn($exception->getMessage());
            if ($exception->getCode() === 409) {
                $this->comment('If you want to overwrite the file supply the --force flag.');
            }

            return (int) $exception->getCode();
        }
    }
}
