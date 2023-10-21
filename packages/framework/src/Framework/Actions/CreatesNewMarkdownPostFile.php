<?php

declare(strict_types=1);

namespace Hyde\Framework\Actions;

use Hyde\Framework\Exceptions\FileConflictException;
use Hyde\Facades\Filesystem;
use Hyde\Pages\MarkdownPost;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * Offloads logic for the make:post command.
 *
 * This class is executed when creating a new Markdown Post
 * using the Hyde command, and converts and formats the
 * data input by the user, and then saves the file.
 *
 * @see \Hyde\Console\Commands\MakePostCommand
 */
class CreatesNewMarkdownPostFile
{
    protected string $title;
    protected string $description;
    protected string $category;
    protected string $author;
    protected string $date;
    protected string $identifier;
    protected ?string $customContent;

    /**
     * Construct the class.
     *
     * @param  string  $title  The Post Title.
     * @param  string|null  $description  The Post Meta Description.
     * @param  string|null  $category  The Primary Post Category.
     * @param  string|null  $author  The Username of the Author.
     */
    public function __construct(string $title, ?string $description, ?string $category, ?string $author, ?string $date = null, ?string $customContent = null)
    {
        $this->title = $title;
        $this->description = $description ?? 'A short description used in previews and SEO';
        $this->category = $category ?? 'blog';
        $this->author = $author ?? 'default';
        $this->customContent = $customContent;

        $this->date = Carbon::make($date ?? Carbon::now())->format('Y-m-d H:i');
        $this->identifier = Str::slug($title);
    }

    /**
     * Save the class object to a Markdown file.
     *
     * @param  bool  $force  Should the file be created even if a file with the same path already exists?
     * @return string Returns the path to the created file.
     *
     * @throws FileConflictException if a file with the same identifier already exists and the force flag is not set.
     */
    public function save(bool $force = false): string
    {
        $page = new MarkdownPost($this->identifier, $this->toArray(), $this->customContent ?? '## Write something awesome.');

        if ($force !== true && Filesystem::exists($page->getSourcePath())) {
            throw new FileConflictException($page->getSourcePath());
        }

        return $page->save()->getSourcePath();
    }

    /**
     * Get the class data as an array.
     *
     * The identifier property is removed from the array as it can't be set in the front matter.
     *
     * @return array{title: string, description: string, category: string, author: string, date: string}
     */
    public function toArray(): array
    {
        return [
            'title' => $this->title,
            'description' => $this->description,
            'category' => $this->category,
            'author' => $this->author,
            'date' => $this->date,
        ];
    }

    public function getIdentifier(): string
    {
        return $this->identifier;
    }
}
