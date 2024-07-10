<?php

declare(strict_types=1);

interface FileAnalyserContract
{
    public function __construct(string $file, string $contents);

    public function run(string $file, string $contents): void;
}

interface LineAnalyserContract
{
    public function __construct(string $file, int $lineNumber, string $line);

    public function run(string $file, int $lineNumber, string $line): void;
}

abstract class Analyser
{
    protected function fail(string $error): void
    {
        HydeStan::getInstance()->addError($error);
    }
}

abstract class FileAnalyser extends Analyser implements FileAnalyserContract
{
    public function __construct(protected string $file, protected string $contents)
    {
        //
    }
}

abstract class LineAnalyser extends Analyser implements LineAnalyserContract
{
    public function __construct(protected string $file, protected int $lineNumber, protected string $line)
    {
        //
    }
}
