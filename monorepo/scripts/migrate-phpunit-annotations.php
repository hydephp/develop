<?php

declare(strict_types=1);

/**
 * PHPUnit Doc Comment to Attributes Migration Script.
 *
 * This script safely converts PHPUnit doc comment metadata to attributes
 * to eliminate deprecation warnings in PHPUnit 11+ and prepare for PHPUnit 12.
 *
 * Usage: php migrate-phpunit-annotations.php [--dry-run] [--path=path/to/tests]
 */
class PHPUnitAnnotationMigrator
{
    private bool $dryRun = false;
    private string $basePath;
    private array $stats = [
        'files_processed' => 0,
        'files_modified' => 0,
        'annotations_converted' => 0,
        'errors' => [],
    ];

    private array $attributeMap = [
        'covers' => 'CoversClass',
        'coversDefaultClass' => 'CoversDefaultClass',
        'coversNothing' => 'CoversNothing',
        'dataProvider' => 'DataProvider',
        'depends' => 'Depends',
        'group' => 'Group',
        'small' => 'Small',
        'medium' => 'Medium',
        'large' => 'Large',
        'runInSeparateProcess' => 'RunInSeparateProcess',
        'preserveGlobalState' => 'PreserveGlobalState',
        'backupGlobals' => 'BackupGlobals',
        'backupStaticAttributes' => 'BackupStaticAttributes',
        'doesNotPerformAssertions' => 'DoesNotPerformAssertions',
        'test' => 'Test',
    ];

    public function __construct(string $basePath)
    {
        $this->basePath = rtrim($basePath, '/');
    }

    public function setDryRun(bool $dryRun): void
    {
        $this->dryRun = $dryRun;
    }

    public function migrate(): array
    {
        $this->output("Starting PHPUnit annotation migration...\n");
        $this->output("Base path: {$this->basePath}\n");
        $this->output('Dry run: '.($this->dryRun ? 'Yes' : 'No')."\n\n");

        $testFiles = $this->findTestFiles();

        foreach ($testFiles as $file) {
            $this->processFile($file);
        }

        $this->printSummary();

        return $this->stats;
    }

    private function findTestFiles(): array
    {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($this->basePath, RecursiveDirectoryIterator::SKIP_DOTS)
        );

        $testFiles = [];
        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                // Only process test files
                if (str_contains($file->getPathname(), 'tests/') ||
                    str_ends_with($file->getFilename(), 'Test.php')) {
                    $testFiles[] = $file->getPathname();
                }
            }
        }

        return $testFiles;
    }

    private function processFile(string $filePath): void
    {
        $this->stats['files_processed']++;

        $content = file_get_contents($filePath);
        if ($content === false) {
            $this->stats['errors'][] = "Could not read file: $filePath";

            return;
        }

        $originalContent = $content;
        $modified = false;
        $requiredImports = [];

        // Process class-level doc comments
        $content = $this->processClassDocComments($content, $requiredImports, $modified);

        // Process method-level doc comments
        $content = $this->processMethodDocComments($content, $requiredImports, $modified);

        // Add required imports if any attributes were added
        if ($modified && ! empty($requiredImports)) {
            $content = $this->addRequiredImports($content, $requiredImports);
        }

        if ($modified) {
            $this->stats['files_modified']++;

            if (! $this->dryRun) {
                if (file_put_contents($filePath, $content) === false) {
                    $this->stats['errors'][] = "Could not write file: $filePath";

                    return;
                }
            }

            $relativePath = str_replace($this->basePath.'/', '', $filePath);
            $this->output("✓ Modified: $relativePath\n");
        }
    }

    private function processClassDocComments(string $content, array &$requiredImports, bool &$modified): string
    {
        // Match class doc comments
        $pattern = '/(?P<before>^(?:\s*\/\*\*[\s\S]*?\*\/\s*)?(?P<attributes>(?:#\[[\s\S]*?\]\s*)*))(?P<classdef>(?:abstract\s+|final\s+)?class\s+\w+)/m';

        $result = preg_replace_callback($pattern, function ($matches) use (&$requiredImports, &$modified) {
            $before = $matches['before'] ?? '';
            $existingAttributes = $matches['attributes'] ?? '';
            $classDefinition = $matches['classdef'] ?? '';

            // Extract and convert doc comment annotations
            $newAttributes = '';
            $docComment = '';

            if (preg_match('/\/\*\*([\s\S]*?)\*\//', $before, $docMatches)) {
                $docComment = $docMatches[1];
                $newAttributes = $this->convertDocCommentToAttributes($docComment, $requiredImports, $modified);

                // Remove the processed annotations from doc comment
                $cleanedDocComment = $this->removeProcessedAnnotations($docComment);

                // If doc comment is now empty or only has whitespace/basic description, remove it
                if (empty(trim($cleanedDocComment)) || $this->isOnlyBasicDescription($cleanedDocComment)) {
                    $before = preg_replace('/\/\*\*[\s\S]*?\*\/\s*/', '', $before);
                } else {
                    $before = preg_replace('/\/\*\*([\s\S]*?)\*\//', "/**$cleanedDocComment*/", $before);
                }
            }

            return $before.$existingAttributes.$newAttributes.$classDefinition;
        }, $content);

        return $result !== null ? $result : $content;
    }

    private function processMethodDocComments(string $content, array &$requiredImports, bool &$modified): string
    {
        // Match method doc comments
        $pattern = '/(?P<before>^(\s*)\/\*\*[\s\S]*?\*\/\s*)?(?P<attributes>(?:#\[[\s\S]*?\]\s*)*)(?P<methoddef>(?:public|protected|private)?\s*(?:static\s+)?function\s+\w+)/m';

        $result = preg_replace_callback($pattern, function ($matches) use (&$requiredImports, &$modified) {
            $before = $matches['before'] ?? '';
            $existingAttributes = $matches['attributes'] ?? '';
            $methodDefinition = $matches['methoddef'] ?? '';
            $indentation = '';

            // Extract indentation from the method definition line
            if (preg_match('/^(\s*)/', $methodDefinition, $indentMatches)) {
                $indentation = $indentMatches[1];
            }

            // Extract and convert doc comment annotations
            $newAttributes = '';

            if (preg_match('/\/\*\*([\s\S]*?)\*\//', $before, $docMatches)) {
                $docComment = $docMatches[1];
                $newAttributes = $this->convertDocCommentToAttributes($docComment, $requiredImports, $modified, $indentation);

                // Remove the processed annotations from doc comment
                $cleanedDocComment = $this->removeProcessedAnnotations($docComment);

                // If doc comment is now empty or only has whitespace/basic description, remove it
                if (empty(trim($cleanedDocComment)) || $this->isOnlyBasicDescription($cleanedDocComment)) {
                    $before = preg_replace('/^(\s*)\/\*\*[\s\S]*?\*\/\s*/m', '$1', $before);
                } else {
                    $before = preg_replace('/\/\*\*([\s\S]*?)\*\//', "/**$cleanedDocComment*/", $before);
                }
            }

            return $before.$existingAttributes.$newAttributes.$methodDefinition;
        }, $content);

        return $result !== null ? $result : $content;
    }

    private function convertDocCommentToAttributes(string $docComment, array &$requiredImports, bool &$modified, string $indentation = ''): string
    {
        $attributes = '';
        $conversions = 0;

        foreach ($this->attributeMap as $annotation => $attributeClass) {
            $pattern = "/@{$annotation}(?:\s+([^\r\n]+))?/";

            if (preg_match_all($pattern, $docComment, $matches, PREG_SET_ORDER)) {
                foreach ($matches as $match) {
                    $value = trim($match[1] ?? '');

                    // Handle special cases
                    $attributeValue = $this->formatAttributeValue($annotation, $value);

                    if ($attributeValue !== null) {
                        $fullAttributeClass = "PHPUnit\\Framework\\Attributes\\$attributeClass";
                        $requiredImports[$attributeClass] = $fullAttributeClass;

                        if ($attributeValue === '') {
                            $attributes .= "{$indentation}#[$attributeClass]\n";
                        } else {
                            $attributes .= "{$indentation}#[$attributeClass($attributeValue)]\n";
                        }

                        $conversions++;
                    }
                }
            }
        }

        if ($conversions > 0) {
            $modified = true;
            $this->stats['annotations_converted'] += $conversions;
        }

        return $attributes;
    }

    private function formatAttributeValue(string $annotation, string $value): ?string
    {
        if (empty($value)) {
            // Annotations that don't take parameters
            if (in_array($annotation, ['test', 'coversNothing', 'small', 'medium', 'large', 'runInSeparateProcess', 'doesNotPerformAssertions'])) {
                return '';
            }

            return null;
        }

        switch ($annotation) {
            case 'covers':
            case 'coversDefaultClass':
                // Handle class/method coverage
                if (str_starts_with($value, '\\')) {
                    return "'".addslashes($value)."'";
                }

                return "'".addslashes($value)."'";

            case 'dataProvider':
                return "'".addslashes($value)."'";

            case 'depends':
                return "'".addslashes($value)."'";

            case 'group':
                return "'".addslashes($value)."'";

            case 'preserveGlobalState':
            case 'backupGlobals':
            case 'backupStaticAttributes':
                // These take boolean values
                $boolValue = strtolower(trim($value));
                if (in_array($boolValue, ['true', '1', 'yes', 'enabled'])) {
                    return 'true';
                } elseif (in_array($boolValue, ['false', '0', 'no', 'disabled'])) {
                    return 'false';
                }

                return 'true'; // Default to true

            default:
                return "'".addslashes($value)."'";
        }
    }

    private function removeProcessedAnnotations(string $docComment): string
    {
        foreach (array_keys($this->attributeMap) as $annotation) {
            $pattern = "/^\s*\*\s*@{$annotation}(?:\s+[^\r\n]+)?\s*$/m";
            $docComment = preg_replace($pattern, '', $docComment);
        }

        // Clean up empty lines
        $docComment = preg_replace('/^\s*\*\s*$/m', '', $docComment);
        $docComment = preg_replace('/\n\s*\n/', "\n", $docComment);

        return $docComment;
    }

    private function isOnlyBasicDescription(string $docComment): bool
    {
        // Remove comment markers and whitespace
        $cleaned = preg_replace('/^\s*\*\s?/m', '', $docComment);
        $cleaned = trim($cleaned);

        // Check if it only contains basic description without any special annotations
        return ! preg_match('/@\w+/', $cleaned) && strlen($cleaned) < 200;
    }

    private function addRequiredImports(string $content, array $requiredImports): string
    {
        $useStatements = [];
        foreach ($requiredImports as $alias => $fullClass) {
            $useStatements[] = "use $fullClass;";
        }

        // Find the position to insert use statements (after existing use statements or after namespace)
        if (preg_match('/^use\s+[^;]+;$/m', $content)) {
            // Insert after the last use statement
            $content = preg_replace('/(^use\s+[^;]+;$)/m', '$1', $content, 1);
            $content = preg_replace('/((?:^use\s+[^;]+;\s*\n)+)/m', '$1'.implode("\n", $useStatements)."\n", $content, 1);
        } elseif (preg_match('/^namespace\s+[^;]+;$/m', $content)) {
            // Insert after namespace
            $content = preg_replace('/(^namespace\s+[^;]+;$)/m', '$1'."\n\n".implode("\n", $useStatements), $content, 1);
        } else {
            // Insert at the beginning after <?php
            $content = preg_replace('/^(<\?php\s*(?:declare\([^)]+\);\s*)?)/m', '$1'."\n\n".implode("\n", $useStatements)."\n", $content, 1);
        }

        return $content;
    }

    private function output(string $message): void
    {
        echo $message;
    }

    private function printSummary(): void
    {
        $this->output("\n".str_repeat('=', 50)."\n");
        $this->output("Migration Summary:\n");
        $this->output("Files processed: {$this->stats['files_processed']}\n");
        $this->output("Files modified: {$this->stats['files_modified']}\n");
        $this->output("Annotations converted: {$this->stats['annotations_converted']}\n");

        if (! empty($this->stats['errors'])) {
            $this->output("\nErrors:\n");
            foreach ($this->stats['errors'] as $error) {
                $this->output("  ✗ $error\n");
            }
        }

        if ($this->dryRun) {
            $this->output("\n📋 This was a dry run. No files were actually modified.\n");
            $this->output("Run without --dry-run to apply changes.\n");
        } else {
            $this->output("\n✅ Migration completed successfully!\n");
        }
    }
}

// Command line interface
function main(): void
{
    global $argv;
    $dryRun = false;
    $path = getcwd();

    // Parse command line arguments
    $args = array_slice($argv ?? [], 1);
    foreach ($args as $arg) {
        if ($arg === '--dry-run') {
            $dryRun = true;
        } elseif (str_starts_with($arg, '--path=')) {
            $path = substr($arg, 7);
        } elseif ($arg === '--help' || $arg === '-h') {
            echo "PHPUnit Doc Comment to Attributes Migration Script\n\n";
            echo "Usage: php migrate-phpunit-annotations.php [options]\n\n";
            echo "Options:\n";
            echo "  --dry-run    Show what would be changed without modifying files\n";
            echo "  --path=PATH  Specify the base path to search for test files\n";
            echo "  --help, -h   Show this help message\n\n";
            echo "Examples:\n";
            echo "  php migrate-phpunit-annotations.php --dry-run\n";
            echo "  php migrate-phpunit-annotations.php --path=packages/framework/tests\n";
            echo "  php migrate-phpunit-annotations.php --path=packages\n";
            exit(0);
        }
    }

    if (! is_dir($path)) {
        echo "Error: Path '$path' does not exist or is not a directory.\n";
        exit(1);
    }

    $migrator = new PHPUnitAnnotationMigrator($path);
    $migrator->setDryRun($dryRun);
    $migrator->migrate();
}

// Run the script if executed directly
if (basename(__FILE__) === basename($_SERVER['SCRIPT_NAME'])) {
    main();
}
