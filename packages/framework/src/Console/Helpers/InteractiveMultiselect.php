<?php

declare(strict_types=1);

namespace Hyde\Console\Helpers;

use Laravel\Prompts\MultiSelectPrompt;

use function array_filter;
use function array_keys;
use function array_values;
use function in_array;

/**
 * When $allLabel is given, checking that sentinel row means "everything", regardless of which other
 * rows are checked; the sentinel is resolved away before the selected keys are returned to the caller.
 *
 * @internal This helper is scoped to the publish command flows and should not be used elsewhere.
 */
class InteractiveMultiselect
{
    /** The sentinel key for the "All" row; option keys are file paths, so this never collides. */
    protected const ALL = '__hyde_select_all__';

    /**
     * @param  array<string, string>  $options  Map of option key => display label.
     * @param  string|null  $allLabel  Label for the "select all" row, or null to omit it entirely.
     * @return array<string> The selected option keys (never includes the sentinel).
     */
    public static function select(string $label, array $options, ?string $allLabel = null): array
    {
        $choices = $allLabel !== null ? [self::ALL => $allLabel] + $options : $options;

        $prompt = new MultiSelectPrompt($label, $choices, [], 10, 'required', hint: 'Navigate with arrow keys, space to select, enter to confirm.');

        $selected = $prompt->prompt();

        // Selecting the sentinel means "everything", regardless of which other rows were checked.
        if (in_array(self::ALL, $selected, true)) {
            return array_keys($options);
        }

        return array_values(array_filter($selected, fn (string $key): bool => $key !== self::ALL));
    }
}
