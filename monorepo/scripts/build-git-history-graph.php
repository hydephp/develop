<?php

declare(strict_types=1);

/**
 * @internal This script is used to build the Git history graphs.
 *
 * @link https://hydephp.github.io/develop/master/git/history-graph.html
 * @link https://hydephp.github.io/develop/master/git/history-graph.txt
 */
echo 'Building the Git history graph...'.PHP_EOL;

if (! file_exists(__DIR__.'/graphs')) {
    mkdir(__DIR__.'/graphs');
}

echo 'Building the plaintext Git history graph... (This may take a while)'.PHP_EOL;
$text = shell_exec('git log --graph --oneline --all');
echo 'Saving the plaintext Git history graph...'.PHP_EOL;
file_put_contents(__DIR__.'/graphs/history-graph.txt', $text);
unset($text); // Free up memory

echo 'Building the HTML Git history graph... (This may take a while)'.PHP_EOL;
$html = shell_exec('git log --graph --oneline --all --color=always');
echo 'Converting ANSI color codes to HTML...'.PHP_EOL;
$html = processHtml($html);
$html = wrapHtml($html);
echo  'Saving the HTML Git history graph...'.PHP_EOL;
file_put_contents(__DIR__.'/graphs/history-graph.html', $html);

echo 'Git history graphs built successfully!'.PHP_EOL;

function processHtml(string $html): string
{
    // We need to run the ANSI to HTML conversion in chunks to prevent memory issues

    $html = explode("\n", $html);

    $chunks = [];

    $chunk = '';

    $chunkSize = 100000;

    foreach ($html as $line) {
        $chunk .= $line."\n";

        if (strlen($chunk) > $chunkSize) {
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
    $ansi = preg_replace('/\x1b\[(\d+)(;\d+)*m/', '</span><span style="color: $1">', $ansi);

    $colors = [
        1 => '#800000',
        30 => '#000000',
        31 => '#800000',
        32 => '#008000',
        33 => '#808000',
        34 => '#000080',
        35 => '#800080',
        36 => '#008080',
        37 => '#c0c0c0',
        90 => '#808080',
        91 => '#ff0000',
        92 => '#00ff00',
        93 => '#ffff00',
        94 => '#0000ff',
        95 => '#ff00ff',
        96 => '#00ffff',
        97 => '#ffffff',
    ];

    $ansi = preg_replace_callback('/<span style="color: (\d+)">/', function ($matches) use ($colors) {
        return '<span style="color: '.$colors[$matches[1]].'">';
    }, $ansi);

    $ansi = str_replace("\033[m", '</span>', $ansi);
    $ansi = '<span style="color: #fff">'.$ansi;
    $ansi .= '</span>';

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
