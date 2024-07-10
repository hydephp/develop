<?php

declare(strict_types=1);

class AnalysisStatisticsContainer
{
    private static int $linesCounted = 0;
    private static float $expressionsAnalysed = 0;

    public static function countedLine(): void
    {
        self::$linesCounted++;
    }

    public static function countedLines(int $count): void
    {
        self::$linesCounted += $count;
    }

    public static function analysedExpression(): void
    {
        self::$expressionsAnalysed++;
    }

    public static function analysedExpressions(float $countOrEstimate): void
    {
        self::$expressionsAnalysed += $countOrEstimate;
    }

    public static function getLinesCounted(): int
    {
        return self::$linesCounted;
    }

    public static function getExpressionsAnalysed(): int
    {
        return (int) round(self::$expressionsAnalysed);
    }
}

function check_str_contains_any(array $searches, string $line): bool
{
    $strContainsAny = false;
    foreach ($searches as $search) {
        AnalysisStatisticsContainer::analysedExpression();
        if (str_contains($line, $search)) {
            $strContainsAny = true;
        }
    }

    return $strContainsAny;
}

function fileLink(string $file, ?int $line = null): string
{
    $path = (realpath(__DIR__.'/../../packages/framework/'.$file) ?: $file).($line ? ':'.$line : '');
    $trim = strlen(getcwd()) + 2;
    $path = substr($path, $trim);

    return str_replace('\\', '/', $path);
}

function recursiveFileFinder(string $directory): array
{
    $files = [];

    $directory = new RecursiveDirectoryIterator(BASE_PATH.'/'.$directory);
    $iterator = new RecursiveIteratorIterator($directory);
    $regex = new RegexIterator($iterator, '/^.+\.php$/i', RecursiveRegexIterator::GET_MATCH);

    foreach ($regex as $file) {
        $files[] = substr($file[0], strlen(BASE_PATH) + 1);
    }

    return $files;
}
