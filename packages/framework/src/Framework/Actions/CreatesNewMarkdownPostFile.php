<?php

declare(strict_types=1);

namespace Hyde\Framework\Actions;

use Hyde\Framework\Exceptions\FileConflictException;
use Hyde\Hyde;
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
    /**
     * The Post Title.
     *
     * @var string
     */
    public string $title;

    /**
     * The Post Meta Description.
     *
     * @var string
     */
    public string $description;

    /**
     * The Primary Post Category.
     *
     * @var string
     */
    public string $category;

    /**
     * The Username of the Author.
     *
     * @var string
     */
    public string $author;

    /**
     * The Publishing Date.
     *
     * @var string
     */
    public string $date;

    /**
     * The Post Slug.
     *
     * @var string
     */
    public string $slug;

    /**
     * Construct the class.
     *
     * @param  string  $title  The Post Title.
     * @param  string|null  $description  The Post Meta Description.
     * @param  string|null  $category  The Primary Post Category.
     * @param  string|null  $author  The Username of the Author.
     * @param  string|null  $date  The Publishing Date.
     * @param  string|null  $slug  The Post Slug.
     */
    public function __construct(
        string $title,
        ?string $description,
        ?string $category,
        ?string $author,
        ?string $date = null,
        ?string $slug = null
    ) {
        $this->title = $title;
        $this->description = $description ?? 'A short description used in previews and SEO';
        $this->category = $category ?? 'blog';
        $this->author = $author ?? 'Mr. Hyde';
        if ($date === null) {
            $this->date = date('Y-m-d H:i');
        }
        if ($slug === null) {
            $this->slug = Str::slug($title);
        }
    }

    /**
     * Save the class object to a Markdown file.
     *
     * @param  bool  $force  Should the file be created even if a file with the same path already exists?
     * @return string|false Returns the path to the file if successful, or false if the file could not be saved.
     *
     * @throws FileConflictException if a file with the same slug already exists and the force flag is not set.
     */
    public function save(bool $force = false): string|false
    {
        $path = Hyde::path("_posts/$this->slug.md");

        if ($force !== true && file_exists($path)) {
            throw new FileConflictException($path);
        }

        $arrayWithoutSlug = ((array) $this);

        unset($arrayWithoutSlug['slug']);

        $contents = (new ConvertsArrayToFrontMatter)->execute($arrayWithoutSlug).
            "\n## Write something awesome.\n\n";

        return file_put_contents($path, $contents) ? $path : false;
    }
}
