<?php

declare(strict_types=1);

/**
 * @internal This script is used to build the Git history graphs.
 *
 * @todo The HTML page has serious memory issues and may need pagination.
 *
 * @link https://hydephp.github.io/develop/master/git/history-graph.html
 * @link https://hydephp.github.io/develop/master/git/history-graph.txt
 */
const CHUNK_SIZE = 100000;
const GRAPHS_DIR = __DIR__.'/graphs';
const TEMP_FILE = 'chunks.temp';

echo 'Building the Git history graph...'.PHP_EOL;

createGraphsDirectoryIfNotExists();
buildPlainTextGraph();
buildHtmlGraph();

echo 'Git history graphs built successfully!'.PHP_EOL;

function createGraphsDirectoryIfNotExists(): void
{
    if (! file_exists(GRAPHS_DIR)) {
        mkdir(GRAPHS_DIR);
    }
}

function buildPlainTextGraph(): void
{
    echo 'Building the plaintext Git history graph... (This may take a while)'.PHP_EOL;
    $text = cached_shell_exec('git log --graph --oneline --all');
    echo 'Saving the plaintext Git history graph...'.PHP_EOL;
    file_put_contents(GRAPHS_DIR.'/history-graph.txt', $text);
}

function buildHtmlGraph(): void
{
    echo 'Building the HTML Git history graph... (This may take a while)'.PHP_EOL;
    $html = cached_shell_exec('git log --graph --oneline --all --color=always');
    echo 'Converting ANSI color codes to HTML...'.PHP_EOL;
    $html = processHtml($html);
    echo 'Generating header...';
    $graph = file_get_contents(GRAPHS_DIR.'/history-graph.txt');
    $header = generateHeader($graph);
    echo ' Done.'.PHP_EOL;
    echo 'Wrapping the HTML...';
    $html = wrapHtml($html, $header);
    echo ' Done.'.PHP_EOL;
    echo 'Saving the HTML Git history graph...'.PHP_EOL;
    file_put_contents(GRAPHS_DIR.'/history-graph.html', $html);
}

function processHtml(string $html): string
{
    $chunks = chunkHtml($html);
    $message = 'Processing '.count($chunks).' chunks...';
    echo $message;

    foreach ($chunks as $index => $chunk) {
        echo "\033[0K\rProcessing chunk ".($index + 1).' of '.count($chunks).'...';
        $chunkHtml = ansiToHtml($chunk);
        $chunkHtml = postProcessChunk($chunkHtml);

        // Since this process takes so much memory, we store the chunks on disk instead of memory.
        file_put_contents(TEMP_FILE, $chunkHtml, FILE_APPEND);
    }

    $html = file_get_contents(TEMP_FILE);
    unlink(TEMP_FILE);
    echo PHP_EOL;

    return $html;
}

function chunkHtml(string $html): array
{
    $lines = explode("\n", $html);
    $chunks = [];
    $chunk = '';

    foreach ($lines as $line) {
        $chunk .= $line."\n";
        if (strlen($chunk) > CHUNK_SIZE) {
            $chunks[] = $chunk;
            $chunk = '';
        }
    }

    if ($chunk !== '') {
        $chunks[] = $chunk;
    }

    return $chunks;
}

function ansiToHtml(string $ansi): string
{
    $colors = [
        1 => '#C50F1F',
        30 => '#0C0C0C',
        31 => '#C50F1F',
        32 => '#13A10E',
        33 => '#C19C00',
        34 => '#0037DA',
        35 => '#881798',
        36 => '#3A96DD',
        37 => '#CCCCCC',
        90 => '#808080',
        91 => '#ff0000',
        92 => '#00ff00',
        93 => '#ffff00',
        94 => '#0000ff',
        95 => '#ff00ff',
        96 => '#00ffff',
        97 => '#ffffff',
    ];

    $ansi = preg_replace('/\x1b\[(\d+)(;\d+)*m/', '</span><span style="color: $1">', $ansi);
    $ansi = preg_replace_callback('/<span style="color: (\d+)">/', function (array $matches) use ($colors): string {
        return '<span style="color: '.$colors[$matches[1]].'">';
    }, $ansi);
    $ansi = str_replace("\033[m", '</span>', $ansi);

    return trim($ansi);
}

function wrapHtml(string $html, string $header): string
{
    return <<<HTML
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Git History Graph</title>
        <style>
            body {
                background-color: #121212;
                color: #fff;
            }
            header, main {
                font-family: monospace;
                white-space: pre;
            }
        </style>
    </head>
    <body>
    <header>$header</header>
    <main>
    $html
    </main>
    </body>
    HTML;
}

function generateHeader(string $text): string
{
    $commitCount = countCommitLines($text);
    $head = trim(shell_exec('git rev-parse --short HEAD'));
    $date = date('Y-m-d H:i:s');

    return <<<HTML
    Git History Graph
    ================
    repository: https://github.com/hydephp/develop.git
    branch: master
    head: $head
    date: $date
    commits: $commitCount
    ----------------
    HTML;
}

function countCommitLines(string $text): int
{
    $count = 0;
    $lines = explode("\n", $text);

    foreach ($lines as $line) {
        if (str_starts_with(ltrim($line, ' /\\|'), '*')) {
            $count++;
        }
    }

    return $count;
}

function postProcessChunk(string $chunk): string
{
    $lines = explode("\n", $chunk);

    $ignore = [
        'Upload documentation preview for PR',
        'Upload live reports from test suite run',
        'Upload site preview from test suite run with ref',
        'Upload coverage report from test suite run with ref',
        'Upload API documentation from test suite run with ref',
    ];

    foreach ($lines as $index => $line) {
        $line = cleanSpanTags($line);
        $line = cleanAsterisk($line);
        // assertValidLine($line, $index);

        if (shouldIgnoreLine($line, $ignore)) {
            $line = '';
        }

        $lines[$index] = trim($line);
    }

    return implode("\n", array_filter($lines));
}

function cleanSpanTags(string $line): string
{
    $line = str_replace(
        ['</span></span>', '</span> </span>', '</span>  </span>', '</span>   </span>'],
        ['</span>', '</span> ', '</span>  ', '</span>   '],
        $line
    );

    if (str_starts_with($line, '</span>')) {
        $line = substr($line, 7);
    }

    if (str_starts_with($line, ' </span>')) {
        $line = substr($line, 8).' ';
    }

    return $line;
}

function cleanAsterisk(string $line): string
{
    if (preg_match('/^\*\s*<\/span>/', $line)) {
        $line = '* '.preg_replace('/^\*\s*<\/span>/', '', $line);
    }

    return $line;
}

function assertValidLine(string $line, int $index): void
{
    $trimmedLine = trim($line);
    assert(! str_starts_with($trimmedLine, '</span>'), "Line $index starts with closing span tag");

    $openTags = substr_count($line, '<span');
    $closeTags = substr_count($line, '</span>');
    assert($openTags === $closeTags, "Line $index has $openTags opening and $closeTags closing span tags");
}

function shouldIgnoreLine(string $line, array $ignore): bool
{
    foreach ($ignore as $str) {
        if (str_contains($line, $str)) {
            return true;
        }
    }

    return false;
}

function cached_shell_exec(string $command): string
{
    $cacheFile = __DIR__.'/cache/'.sha1($command).'.txt';
    $cache = file_exists($cacheFile) ? file_get_contents($cacheFile) : '';
    $output = $cache ?: shell_exec($command);
    file_put_contents($cacheFile, $output);

    return $output;
}
