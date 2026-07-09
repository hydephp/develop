<?php

declare(strict_types=1);

namespace Hyde\RealtimeCompiler\Http;

use Hyde\Hyde;
use Desilva\Microserve\Response;
use Desilva\Microserve\JsonResponse;
use Illuminate\Support\Facades\Process;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Handles the "Open in Editor" button on the exception page, opening a file
 * referenced in a stack trace frame in the system's default editor.
 */
class OpenInEditorController extends BaseController
{
    protected bool $withSession = true;

    /** @var array<int, string> File extensions that may be opened, since HydePHP errors often trace back to content files rather than PHP source. */
    protected const ALLOWED_EXTENSIONS = ['.php', '.md', '.markdown', '.yaml', '.yml', '.json', '.blade.php'];

    public static function enabled(): bool
    {
        return config('hyde.server.dashboard.interactive', true);
    }

    public function handle(): Response
    {
        if ($this->request->method !== 'POST') {
            return $this->sendJsonErrorResponse(405, 'Method not allowed');
        }

        if (! static::enabled()) {
            return $this->sendJsonErrorResponse(403, 'Enable `server.dashboard.interactive` in `config/hyde.php` to open files from the error page.');
        }

        try {
            $this->authorizePostRequest();

            $file = $this->request->data['file'] ?? $this->abort(400, 'Must provide a file path');
            $path = $this->resolvePath($file) ?? $this->abort(403, 'Refusing to open a file outside the project directory');

            if (! $this->hasAllowedExtension($path)) {
                $this->abort(403, 'Refusing to open a file with a disallowed extension');
            }

            if (! is_file($path)) {
                $this->abort(404, 'File not found');
            }

            Process::run([...$this->findGeneralOpenBinary(), $path])->throw();
        } catch (HttpException $exception) {
            return $this->sendJsonErrorResponse($exception->getStatusCode(), $exception->getMessage());
        }

        return new JsonResponse(200, 'OK', [
            'message' => 'Opened file in editor',
        ]);
    }

    /** Resolve the given path to a real, absolute path, refusing anything outside the project directory. */
    protected function resolvePath(string $file): ?string
    {
        $base = realpath(defined('BASE_PATH') ? BASE_PATH : Hyde::path());

        if ($base === false) {
            return null;
        }

        $isAbsolute = str_starts_with($file, '/') || preg_match('#^[A-Za-z]:[\\\\/]#', $file) === 1;
        $candidate = $isAbsolute ? $file : $base.'/'.$file;

        $real = realpath($candidate);

        if ($real === false || ! str_starts_with($real, $base.DIRECTORY_SEPARATOR)) {
            return null;
        }

        return $real;
    }

    protected function hasAllowedExtension(string $path): bool
    {
        $path = strtolower($path);

        foreach (static::ALLOWED_EXTENSIONS as $extension) {
            if (str_ends_with($path, $extension)) {
                return true;
            }
        }

        return false;
    }

    /** @return array<int, string> */
    protected function findGeneralOpenBinary(): array
    {
        return match (PHP_OS_FAMILY) {
            // Using PowerShell allows us to open the file in the background
            'Windows' => ['powershell', 'Start-Process'],
            'Darwin' => ['open'],
            'Linux' => ['xdg-open'],
            default => $this->abort(500,
                sprintf("Unable to find a matching 'open' binary for OS family '%s'", PHP_OS_FAMILY)
            )
        };
    }
}
