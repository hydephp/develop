<?php

declare(strict_types=1);

namespace Hyde\Framework\Services;

use Hyde\Hyde;

use function Hyde\unixsum_file;
use function str_replace;
use function in_array;
use function unslash;
use function glob;

/**
 * @internal This class may be refactored to better suit its intended purpose.
 *
 * Helper methods to interact with the virtual filecache that is used to compare
 * published Blade views with the original Blade views in the Hyde Framework
 * so the user can be warned before overwriting their customizations.
 */
class ViewDiffService
{
    /** @return array<string, array{unixsum: string}> */
    public static function getViewFileHashIndex(): array
    {
        $filecache = [];

        foreach (glob(Hyde::vendorPath('resources/views/**/*.blade.php')) as $file) {
            $filecache[unslash(str_replace(Hyde::vendorPath(), '', (string) $file))] = [
                'unixsum' => unixsum_file($file),
            ];
        }

        return $filecache;
    }

    /** @return array<string> */
    public static function getChecksums(): array
    {
        $checksums = [];

        foreach (static::getViewFileHashIndex() as $file) {
            $checksums[] = $file['unixsum'];
        }

        return $checksums;
    }

    public static function checksumMatchesAny(string $checksum): bool
    {
        return in_array($checksum, static::getChecksums());
    }
}
