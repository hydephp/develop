<?php

declare(strict_types=1);

/**
 * @internal This script is used to build the Git history graphs.
 *
 * @link https://hydephp.github.io/develop/master/git/history-graph.html
 * @link https://hydephp.github.io/develop/master/git/history-graph.txt
 */

echo 'Building the Git history graph...' . PHP_EOL;

if (!file_exists(__DIR__ . '/graphs')) {
    mkdir(__DIR__ . '/graphs');
}

echo 'Building the plaintext Git history graph... (This may take a while)' . PHP_EOL;
$text = shell_exec('git log --graph --oneline --all');
echo 'Saving the plaintext Git history graph...' . PHP_EOL;
file_put_contents(__DIR__ . '/graphs/history-graph.txt', $text);
unset($text); // Free up memory

echo 'Building the HTML Git history graph... (This may take a while)' . PHP_EOL;
$html = shell_exec('git log --graph --oneline --all --color=always');
echo 'Converting ANSI color codes to HTML...' . PHP_EOL;
$html = processHtml($html);
$html = wrapHtml($html);
echo  'Saving the HTML Git history graph...' . PHP_EOL;
file_put_contents(__DIR__ . '/graphs/history-graph.html', $html);

echo 'Git history graphs built successfully!' . PHP_EOL;

function processHtml(string $html): string
{
    // We need to run the ANSI to HTML conversion in chunks to prevent memory issues

    $html = explode("\n", $html);

    $chunks = [];

    $chunk = '';

    foreach ($html as $line) {
        $chunk .= $line . "\n";

        if (strlen($chunk) > 100000) {
            $chunks[] = $chunk;
            $chunk = '';
        }
    }

    if ($chunk !== '') {
        $chunks[] = $chunk;
    }

    $html = '';

    $message = 'Processing '.count($chunks).' chunks...';
    echo $message;

    foreach ($chunks as $index => $chunk) {
        // Progress indicator
        echo "\033[0K\rProcessing chunk ".($index + 1).' of '.count($chunks).'...';

        $html .= ansiToHtml($chunk);
    }

    echo PHP_EOL;

    return $html;
}

function ansiToHtml(string $ansi): string
{
    $ansi = htmlspecialchars($ansi, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $ansi = preg_replace('/\e\[(\d+)(;\d+)*m/', '</span><span style="color: $1">', $ansi);
    $ansi = '<span>' . $ansi . '</span>';

    return $ansi;
}

function wrapHtml(string $html): string
{
    return <<<HTML
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Git History Graph</title>
        <style>
            body {
                font-family: monospace;
                white-space: pre;
                background-color: #000;
                color: #fff;
                margin: 0;
                padding: 0;
            }
        </style>
    </head>
    <body>
    $html
    </body>
    HTML;
}
