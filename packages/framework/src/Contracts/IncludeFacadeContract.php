<?php

namespace Hyde\Framework\Contracts;

interface IncludeFacadeContract
{
    /**
     * Get the raw contents of a partial file in the includes directory.
     *
     * @param  string  $partial  The name of the partial file, including the extension.
     * @param  string|null  $default  The default value to return if the partial is not found.
     * @return string|null The contents of the partial file, or the default value if not found.
     */
    public static function get(string $partial, ?string $default = null): ?string;

    /**
     * Get the rendered Markdown of a partial file in the includes directory.
     *
     * @param  string  $partial  The name of the partial file, without the extension.
     * @param  string|null  $default  The default value to return if the partial is not found.
     * @return string|null The contents of the partial file, or the default value if not found.
     */
    public static function markdown(string $partial, ?string $default = null): ?string;

    /**
     * Get the rendered Blade of a partial file in the includes directory.
     *
     * @param  string  $partial  The name of the partial file, without the extension.
     * @param  string|null  $default  The default value to return if the partial is not found.
     * @return string|null The contents of the partial file, or the default value if not found.
     */
    public static function blade(string $partial, ?string $default = null): ?string;
}
