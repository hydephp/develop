<?php

namespace Hyde\Framework\Actions;

use Illuminate\Support\Str;

/**
 * Parse the Markdown text to show in the Footer.
 *
 * Tries to convert the Markdown text if supplied in the config,
 * otherwise, it falls back to a default string.
 *
 * @see \Hyde\Framework\Testing\Unit\ConvertsFooterMarkdownTest
 */
class ConvertsFooterMarkdown
{
    /**
     * Execute the action.
     *
     * @return string $html
     */
    public static function execute(): string
    {
        return Str::markdown(config(
            'hyde.footer.markdown',
            'Site built with the Free and Open Source [HydePHP](https://github.com/hydephp/hyde).
		License [MIT](https://github.com/hydephp/hyde/blob/master/LICENSE.md).'
        ));
    }
}
