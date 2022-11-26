<?php

declare(strict_types=1);

namespace Hyde\Support\Contracts;

/**
 * @see \Illuminate\Filesystem\Filesystem
 */
interface FilesystemContract
{
    /**
     * Determine if a file or directory exists.
     *
     * @param string $path
     * @return bool
     */
    public function exists($path);

    /**
     * Determine if a file or directory is missing.
     *
     * @param string $path
     * @return bool
     */
    public function missing($path);

    /**
     * Get the contents of a file.
     *
     * @param string $path
     * @param bool $lock
     * @return string
     *
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function get($path, $lock = false);

    /**
     * Get contents of a file with shared access.
     *
     * @param string $path
     * @return string
     */
    public function sharedGet($path);

    /**
     * Get the returned value of a file.
     *
     * @param string $path
     * @param array $data
     * @return mixed
     *
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function getRequire($path, array $data = []);

    /**
     * Require the given file once.
     *
     * @param string $path
     * @param array $data
     * @return mixed
     *
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function requireOnce($path, array $data = []);

    /**
     * Get the contents of a file one line at a time.
     *
     * @param string $path
     * @return \Illuminate\Support\LazyCollection
     *
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function lines($path);

    /**
     * Get the hash of the file at the given path.
     *
     * @param string $path
     * @param string $algorithm
     * @return string
     */
    public function hash($path, $algorithm = 'md5');

    /**
     * Write the contents of a file.
     *
     * @param string $path
     * @param string $contents
     * @param bool $lock
     * @return int|bool
     */
    public function put($path, $contents, $lock = false);

    /**
     * Write the contents of a file, replacing it atomically if it already exists.
     *
     * @param string $path
     * @param string $content
     * @return void
     */
    public function replace($path, $content);

    /**
     * Replace a given string within a given file.
     *
     * @param array|string $search
     * @param array|string $replace
     * @param string $path
     * @return void
     */
    public function replaceInFile($search, $replace, $path);

    /**
     * Prepend to a file.
     *
     * @param string $path
     * @param string $data
     * @return int
     */
    public function prepend($path, $data);

    /**
     * Append to a file.
     *
     * @param string $path
     * @param string $data
     * @return int
     */
    public function append($path, $data);

    /**
     * Get or set UNIX mode of a file or directory.
     *
     * @param string $path
     * @param int|null $mode
     * @return mixed
     */
    public function chmod($path, $mode = null);

    /**
     * Delete the file at a given path.
     *
     * @param string|array $paths
     * @return bool
     */
    public function delete($paths);

    /**
     * Move a file to a new location.
     *
     * @param string $path
     * @param string $target
     * @return bool
     */
    public function move($path, $target);

    /**
     * Copy a file to a new location.
     *
     * @param string $path
     * @param string $target
     * @return bool
     */
    public function copy($path, $target);

    /**
     * Create a symlink to the target file or directory. On Windows, a hard link is created if the target is a file.
     *
     * @param string $target
     * @param string $link
     * @return void
     */
    public function link($target, $link);

    /**
     * Create a relative symlink to the target file or directory.
     *
     * @param string $target
     * @param string $link
     * @return void
     *
     * @throws \RuntimeException
     */
    public function relativeLink($target, $link);

    /**
     * Extract the file name from a file path.
     *
     * @param string $path
     * @return string
     */
    public function name($path);

    /**
     * Extract the trailing name component from a file path.
     *
     * @param string $path
     * @return string
     */
    public function basename($path);

    /**
     * Extract the parent directory from a file path.
     *
     * @param string $path
     * @return string
     */
    public function dirname($path);

    /**
     * Extract the file extension from a file path.
     *
     * @param string $path
     * @return string
     */
    public function extension($path);

    /**
     * Guess the file extension from the mime-type of a given file.
     *
     * @param string $path
     * @return string|null
     *
     * @throws \RuntimeException
     */
    public function guessExtension($path);

    /**
     * Get the file type of a given file.
     *
     * @param string $path
     * @return string
     */
    public function type($path);

    /**
     * Get the mime-type of a given file.
     *
     * @param string $path
     * @return string|false
     */
    public function mimeType($path);

    /**
     * Get the file size of a given file.
     *
     * @param string $path
     * @return int
     */
    public function size($path);

    /**
     * Get the file's last modification time.
     *
     * @param string $path
     * @return int
     */
    public function lastModified($path);

    /**
     * Determine if the given path is a directory.
     *
     * @param string $directory
     * @return bool
     */
    public function isDirectory($directory);

    /**
     * Determine if the given path is a directory that does not contain any other files or directories.
     *
     * @param string $directory
     * @param bool $ignoreDotFiles
     * @return bool
     */
    public function isEmptyDirectory($directory, $ignoreDotFiles = false);

    /**
     * Determine if the given path is readable.
     *
     * @param string $path
     * @return bool
     */
    public function isReadable($path);

    /**
     * Determine if the given path is writable.
     *
     * @param string $path
     * @return bool
     */
    public function isWritable($path);

    /**
     * Determine if two files are the same by comparing their hashes.
     *
     * @param string $firstFile
     * @param string $secondFile
     * @return bool
     */
    public function hasSameHash($firstFile, $secondFile);

    /**
     * Determine if the given path is a file.
     *
     * @param string $file
     * @return bool
     */
    public function isFile($file);

    /**
     * Find path names matching a given pattern.
     *
     * @param string $pattern
     * @param int $flags
     * @return array
     */
    public function glob($pattern, $flags = 0);

    /**
     * Get an array of all files in a directory.
     *
     * @param string $directory
     * @param bool $hidden
     * @return \Symfony\Component\Finder\SplFileInfo[]
     */
    public function files($directory, $hidden = false);

    /**
     * Get all of the files from the given directory (recursive).
     *
     * @param string $directory
     * @param bool $hidden
     * @return \Symfony\Component\Finder\SplFileInfo[]
     */
    public function allFiles($directory, $hidden = false);

    /**
     * Get all of the directories within a given directory.
     *
     * @param string $directory
     * @return array
     */
    public function directories($directory);

    /**
     * Ensure a directory exists.
     *
     * @param string $path
     * @param int $mode
     * @param bool $recursive
     * @return void
     */
    public function ensureDirectoryExists($path, $mode = 0755, $recursive = true);

    /**
     * Create a directory.
     *
     * @param string $path
     * @param int $mode
     * @param bool $recursive
     * @param bool $force
     * @return bool
     */
    public function makeDirectory($path, $mode = 0755, $recursive = false, $force = false);

    /**
     * Move a directory.
     *
     * @param string $from
     * @param string $to
     * @param bool $overwrite
     * @return bool
     */
    public function moveDirectory($from, $to, $overwrite = false);

    /**
     * Copy a directory from one location to another.
     *
     * @param string $directory
     * @param string $destination
     * @param int|null $options
     * @return bool
     */
    public function copyDirectory($directory, $destination, $options = null);

    /**
     * Recursively delete a directory.
     *
     * The directory itself may be optionally preserved.
     *
     * @param string $directory
     * @param bool $preserve
     * @return bool
     */
    public function deleteDirectory($directory, $preserve = false);

    /**
     * Remove all of the directories within a given directory.
     *
     * @param string $directory
     * @return bool
     */
    public function deleteDirectories($directory);

    /**
     * Empty the specified directory of all files and folders.
     *
     * @param string $directory
     * @return bool
     */
    public function cleanDirectory($directory);
}
