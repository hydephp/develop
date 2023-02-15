<?php

declare(strict_types=1);

namespace Hyde\Framework\Services;

use Hyde\Facades\Filesystem;
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
 * @see \Hyde\Framework\Testing\Feature\Services\ChecksumServiceTest
 */
class ChecksumService
{
    /**
     * @deprecated Will be renamed to getViewFileCache or similar
     *
     * @return array<string, array{unixsum: string}>
     */
    public static function getFilecache(): array
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

    public static function getChecksums(): array
    {
        $cache = static::getFilecache();

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
     * This function is not cryptographically secure.
     *
     * @see https://github.com/hydephp/framework/issues/85
     */
    public static function unixsum(string $string): string
    {
        return md5(str_replace(["\r\n", "\r"], "\n", $string));
    }

    /**
     * Shorthand for {@see static::unixsum()} but loads a file.
     */
    public static function unixsumFile(string $file): string
    {
        return static::unixsum(Filesystem::getContents($file));
    }
}
