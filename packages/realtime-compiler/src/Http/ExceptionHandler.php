<?php

declare(strict_types=1);

namespace Hyde\RealtimeCompiler\Http;

use PhpToken;
use Throwable;
use OutOfBoundsException;
use Desilva\Microserve\Response;
use Composer\InstalledVersions;
use Illuminate\Support\Facades\Blade;

/**
 * Exceptions are caught by the server.php to be handled here,
 * where we render a pretty error page with a 500 HTTP code.
 */
class ExceptionHandler
{
    public static function handle(Throwable $exception): Response
    {
        $statusCode = $exception->getCode() >= 400 ? $exception->getCode() : 500;

        $frames = static::buildFrames($exception);
        $environment = static::buildEnvironment();

        $html = Blade::render(file_get_contents(__DIR__.'/../../resources/error.blade.php'), [
            'exception' => $exception,
            'statusCode' => $statusCode,
            'frames' => $frames,
            'environment' => $environment,
            'report' => static::buildReport($exception, $frames, $environment, $statusCode),
            'openInEditorEnabled' => OpenInEditorController::enabled(),
            'csrfToken' => static::csrfToken(),
        ]);

        return Response::make($statusCode, BaseController::matchStatusCode($statusCode), [
            'Content-Type' => 'text/html',
            'Content-Length' => strlen($html),
            'body' => $html,
        ]);
    }

    /** @return array<int, array{number: int, class: ?string, function: ?string, file: ?string, relativeFile: ?string, line: ?int, snippet: ?array}> */
    protected static function buildFrames(Throwable $exception): array
    {
        // The exception's own throw site is treated as the first, most relevant frame.
        $frames = [[
            'class' => null,
            'function' => null,
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
        ]];

        foreach ($exception->getTrace() as $frame) {
            $frames[] = [
                'class' => $frame['class'] ?? null,
                'function' => $frame['function'] ?? null,
                'file' => $frame['file'] ?? null,
                'line' => $frame['line'] ?? null,
            ];
        }

        return array_values(array_map(function (array $frame, int $index): array {
            $frame['number'] = $index + 1;
            $frame['relativeFile'] = $frame['file'] !== null ? static::relativePath($frame['file']) : null;
            $frame['snippet'] = $frame['file'] !== null ? static::extractSnippet($frame['file'], $frame['line']) : null;

            return $frame;
        }, $frames, array_keys($frames)));
    }

    /** @return array{phpVersion: string, hydeVersion: string, os: string, frameworkVersion: string, rtcVersion: string, request: string, time: string, timeIso: string} */
    protected static function buildEnvironment(): array
    {
        $timestamp = (int) ($_SERVER['REQUEST_TIME_FLOAT'] ?? time());

        return [
            'phpVersion' => PHP_VERSION,
            'hydeVersion' => static::packageVersion('hyde/hyde'),
            'os' => static::operatingSystemName(),
            'frameworkVersion' => static::packageVersion('hyde/framework'),
            'rtcVersion' => static::packageVersion('hyde/realtime-compiler'),
            'request' => sprintf('%s %s', $_SERVER['REQUEST_METHOD'] ?? 'GET', $_SERVER['REQUEST_URI'] ?? '/'),
            // Rendered in the browser's own locale and timezone via Intl.DateTimeFormat; this is the pre-JS/report fallback.
            'time' => date('M j, Y, g:i:s A', $timestamp),
            'timeIso' => date(DATE_ATOM, $timestamp),
        ];
    }

    /**
     * Build a plain-text report of the exception, suitable for pasting into an AI assistant or issue tracker.
     *
     * @param  array<int, array{number: int, class: ?string, function: ?string, file: ?string, relativeFile: ?string, line: ?int, snippet: ?array}>  $frames
     * @param  array{phpVersion: string, hydeVersion: string, os: string, frameworkVersion: string, rtcVersion: string, request: string, time: string, timeIso: string}  $environment
     */
    protected static function buildReport(Throwable $exception, array $frames, array $environment, int $statusCode): string
    {
        $lines = [
            sprintf('%s (%d)', $exception::class, $statusCode),
            $exception->getMessage(),
            '',
            'Stack trace:',
        ];

        foreach ($frames as $frame) {
            $location = $frame['relativeFile'] ?? $frame['file'] ?? '[internal function]';
            $line = $frame['line'] !== null ? ':'.$frame['line'] : '';
            $function = $frame['function'] !== null ? ' '.$frame['function'].'()' : '';

            $lines[] = sprintf('#%d %s%s%s', $frame['number'], $location, $line, $function);
        }

        $lines[] = '';
        $lines[] = 'Environment:';
        $lines[] = sprintf('- PHP: %s', $environment['phpVersion']);
        $lines[] = sprintf('- Hyde: %s', $environment['hydeVersion']);
        $lines[] = sprintf('- Framework: %s', $environment['frameworkVersion']);
        $lines[] = sprintf('- Realtime Compiler: %s', $environment['rtcVersion']);
        $lines[] = sprintf('- OS: %s', $environment['os']);
        $lines[] = sprintf('- Request: %s', $environment['request']);
        $lines[] = sprintf('- Time: %s', $environment['time']);

        return implode("\n", $lines);
    }

    protected static function csrfToken(): string
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        return BaseController::generateCSRFToken();
    }

    protected static function packageVersion(string $package): string
    {
        try {
            return InstalledVersions::getPrettyVersion($package) ?? 'unreleased';
        } catch (OutOfBoundsException) {
            return 'unreleased';
        }
    }

    protected static function operatingSystemName(): string
    {
        return match (PHP_OS_FAMILY) {
            'Darwin' => 'macOS',
            'Windows' => 'Windows',
            'Linux' => 'Linux',
            default => PHP_OS_FAMILY,
        };
    }

    protected static function relativePath(string $path): string
    {
        if (defined('BASE_PATH') && str_starts_with($path, BASE_PATH)) {
            return ltrim(substr($path, strlen(BASE_PATH)), '/\\');
        }

        return $path;
    }

    /** @return array{highlightLine: int, lines: array<int, string>}|null */
    protected static function extractSnippet(string $file, ?int $line, int $padding = 6): ?array
    {
        if ($line === null || ! is_file($file) || ! is_readable($file)) {
            return null;
        }

        $contents = file_get_contents($file);

        if ($contents === false) {
            return null;
        }

        $highlighted = static::highlight($contents);
        $lastLine = array_key_last($highlighted) ?? 1;

        $start = max(1, $line - $padding);
        $end = min($lastLine, $line + $padding);

        $lines = [];
        for ($number = $start; $number <= $end; $number++) {
            $lines[$number] = $highlighted[$number] ?? '';
        }

        return [
            'highlightLine' => $line,
            'lines' => $lines,
        ];
    }

    /** @return array<int, string> Highlighted HTML for each line of code, keyed by 1-indexed line number */
    protected static function highlight(string $code): array
    {
        $lines = [1 => ''];
        $currentLine = 1;

        foreach (PhpToken::tokenize($code) as $token) {
            $class = static::tokenClass($token);
            $segments = explode("\n", $token->text);
            $lastSegment = count($segments) - 1;

            foreach ($segments as $index => $segment) {
                if ($segment !== '') {
                    $escaped = htmlspecialchars($segment, ENT_QUOTES);
                    $lines[$currentLine] .= $class !== null ? "<span class=\"tok-$class\">$escaped</span>" : $escaped;
                }

                if ($index < $lastSegment) {
                    $currentLine++;
                    $lines[$currentLine] ??= '';
                }
            }
        }

        return $lines;
    }

    protected static function tokenClass(PhpToken $token): ?string
    {
        return match (true) {
            $token->is([T_COMMENT, T_DOC_COMMENT]) => 'comment',
            $token->is([T_VARIABLE]) => 'variable',
            $token->is([T_LNUMBER, T_DNUMBER]) => 'number',
            $token->is([T_CONSTANT_ENCAPSED_STRING, T_ENCAPSED_AND_WHITESPACE, T_START_HEREDOC, T_END_HEREDOC]) => 'string',
            $token->is(static::keywordTokens()) => 'keyword',
            $token->id === T_STRING && preg_match('/^[A-Z]/', $token->text) === 1 => 'type',
            default => null,
        };
    }

    /** @return array<int, int> */
    protected static function keywordTokens(): array
    {
        return [
            T_ABSTRACT, T_ARRAY, T_AS, T_BREAK, T_CALLABLE, T_CASE, T_CATCH, T_CLASS,
            T_CLONE, T_CONST, T_CONTINUE, T_DECLARE, T_DEFAULT, T_DO, T_ECHO, T_ELSE, T_ELSEIF,
            T_EMPTY, T_ENDDECLARE, T_ENDFOR, T_ENDFOREACH, T_ENDIF, T_ENDSWITCH, T_ENDWHILE, T_ENUM,
            T_EXIT, T_EXTENDS, T_FINAL, T_FINALLY, T_FN, T_FOR, T_FOREACH, T_FUNCTION, T_GLOBAL,
            T_GOTO, T_IF, T_IMPLEMENTS, T_INCLUDE, T_INCLUDE_ONCE, T_INSTANCEOF, T_INSTEADOF,
            T_INTERFACE, T_ISSET, T_LIST, T_LOGICAL_AND, T_LOGICAL_OR, T_LOGICAL_XOR, T_MATCH,
            T_NAMESPACE, T_NEW, T_PRINT, T_PRIVATE, T_PROTECTED, T_PUBLIC, T_READONLY, T_REQUIRE,
            T_REQUIRE_ONCE, T_RETURN, T_STATIC, T_SWITCH, T_THROW, T_TRAIT, T_TRY, T_UNSET, T_USE,
            T_VAR, T_WHILE, T_YIELD, T_YIELD_FROM,
        ];
    }
}
