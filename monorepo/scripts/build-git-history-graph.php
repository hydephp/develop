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

echo 'Building the HTML Git history graph... (This may take a while)' . PHP_EOL;
$html = shell_exec('git log --graph --oneline --all --color=always');
echo 'Converting ANSI color codes to HTML...' . PHP_EOL;
$html = processHtml($html);
echo  'Saving the HTML Git history graph...' . PHP_EOL;
file_put_contents(__DIR__ . '/graphs/history-graph.html', $html);

echo 'Git history graphs built successfully!' . PHP_EOL;

function processHtml(string $html): string
{
    return ansiToHtml($html);
}

function ansiToHtml(string $ansi): string
{
    $ansi = htmlspecialchars($ansi, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $ansi = preg_replace('/\e\[(\d+)(;\d+)*m/', '</span><span style="color: $1">', $ansi);
    $ansi = preg_replace('/\n/', '<br>', $ansi);
    $ansi = '<span>' . $ansi . '</span>';

    return $ansi;
}
