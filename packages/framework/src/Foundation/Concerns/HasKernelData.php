<?php

declare(strict_types=1);

namespace Hyde\Foundation\Concerns;

use Hyde\Facades\Config;
use Illuminate\Support\Collection;
use Hyde\Framework\Features\Blogging\Models\PostAuthor;

use function strtolower;

/**
 * Contains accessors and containers for data stored in the kernel.
 *
 * @internal Single-use trait for the HydeKernel class.
 *
 * @see \Hyde\Foundation\HydeKernel
 */
trait HasKernelData
{
    /**
     * The collection of authors defined in the config.
     *
     * @var \Illuminate\Support\Collection<string, \Hyde\Framework\Features\Blogging\Models\PostAuthor>
     */
    protected Collection $authors;

    /**
     * Get the collection of authors defined in the config.
     *
     * @return \Illuminate\Support\Collection<string, \Hyde\Framework\Features\Blogging\Models\PostAuthor>
     */
    public function authors(): Collection
    {
        if (isset($this->authors)) {
            return $this->authors;
        }

        $config = new Collection(Config::getArray('hyde.authors', []));

        if ($config->isEmpty()) {
            // Defer setting the authors property until the next try.
            return $config;
        }

        return $this->authors = $this->parseConfigurationAuthors($config);
    }

    protected function parseConfigurationAuthors(Collection $authors): Collection
    {
        return $authors->mapWithKeys(function (PostAuthor $author): array {
            return [strtolower($author->username) => $author];
        });
    }
}
