<?php

declare(strict_types=1);

namespace Hyde\Framework\Services;

use function glob;
use Hyde\Hyde;
use function in_array;
use function md5;
use function str_replace;
use function unslash;

/**
 * @internal This class may be refactored to better suit its intended purpose.
 *
 * Helper methods to interact with the virtual filecache that is used to compare
 * published Blade views with the original Blade views in the Hyde Framework
 * so the user can be warned before overwriting their customizations.
 *
 * @see \Hyde\Framework\Testing\Feature\Services\ViewDiffServiceTest
 */
class ViewDiffService
{
    /** @return array<string, array{unixsum: string}> */
    public static function getViewFileHashIndex(): array
    {
        $filecache = [];

        $files = glob(Hyde::vendorPath('resources/views/**/*.blade.php'));

        foreach ($files as $file) {
            $filecache[unslash(str_replace(Hyde::vendorPath(), '', (string) $file))] = [
                'unixsum' => static::unixsumFile($file),
            ];
        }

        return $filecache;
    }

    /** @return array<string> */
    public static function getChecksums(): array
    {
        $cache = static::getViewFileHashIndex();

        $checksums = [];

        foreach ($cache as $file) {
            $checksums[] = $file['unixsum'];
        }

        return $checksums;
    }

    public static function checksumMatchesAny(string $checksum): bool
    {
        return in_array($checksum, static::getChecksums());
    }

    /**
     * A EOL agnostic wrapper for calculating MD5 checksums.
     *
     * @deprecated TODO: Move to helpers.php
     *
     * This function is not cryptographically secure.
     * @see https://github.com/hydephp/framework/issues/85
     */
    public static function unixsum(string $string): string
    {
        return \Hyde\unixsum($string);
    }

    /**
     * Shorthand for {@see static::unixsum()} but loads a file.
     *
     * @deprecated TODO: Move to helpers.php
     */
    public static function unixsumFile(string $file): string
    {
        return \Hyde\unixsum_file($file);
    }
}
