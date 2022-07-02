<?php

namespace Hyde\Framework\Contracts;

interface MarkdownDocumentContract
{
    /**
     * Construct the class.
     *
     * @param  array  $matter  The parsed front matter.
     * @param  string  $body  The parsed Markdown body.
     */
    public function __construct(array $matter = [], string $body = '');

    /**
     * Get the front matter property for the specified key, or null if it does not exist.
     *
     * @param string $key
     */
    public function __get(string $key);

    /**
     * Get all the front matter as an array, or the property if a key is specified,
     * falling back to the supplied default return value if the key is not found.
     *
     * @param string|null $key
     * @param mixed|null $default
     * @return mixed|null
     */
    public function matter(string $key = null, mixed $default = null): mixed;

    /**
     * Get the Markdown body.
     *
     * @return string
     */
    public function body(): string;

    /**
     * Render the Markdown body into HTML.
     */
    public function render(): string;

    /**
     * Parse a Markdown file and return a new MarkdownDocument instance.
     *
     * @param string $localFilepath
     * @return static<MarkdownDocumentContract>
     */
    public static function parseFile(string $localFilepath): static;
}
