<?php

declare(strict_types=1);

namespace Hyde\Testing\Support\HtmlTesting;

use Hyde\Hyde;
use JetBrains\PhpStorm\NoReturn;
use Illuminate\Support\Collection;

use function e;
use function dd;
use function trim;
use function sprintf;
use function ucfirst;
use function implode;
use function is_array;
use function in_array;
use function array_map;
use function microtime;
use function is_numeric;
use function array_keys;
use function str_repeat;
use function base64_encode;
use function number_format;
use function memory_get_usage;
use function file_put_contents;

/** @internal Single use trait for {@see \Hyde\Testing\Support\HtmlTesting\TestableHtmlDocument} */
trait DumpsDocumentState
{
    public function getStructure(): string
    {
        // Create a structure map of the document, containing only the tag names and their children

        $structure = '';

        $this->nodes->each(function (TestableHtmlElement $node) use (&$structure) {
            $structure .= $this->createStructureMapEntry($node, 0);
        });

        return trim($structure);
    }

    // TODO: Improve complex text state parsing
    public function getTextRepresentation(): string
    {
        $text = [];

        $this->nodes->each(function (TestableHtmlElement $node) use (&$text) {
            $text[] =  $this->createTextMapEntry($node);
        });

        return trim(implode('', $text));
    }

    public function dump(bool $writeHtml = true): string
    {
        $timeStart = microtime(true);
        memory_get_usage(true);

        $html = $this->createAstInspectionDump();

        $timeEnd = number_format((microtime(true) - $timeStart) * 1000, 2);
        $memoryUsage = number_format(memory_get_usage(true) / 1024 / 1024, 2);

        $html .= sprintf("\n<footer><p><small>Generated in %s ms, using %s MB of memory.</small></p></footer>", $timeEnd, $memoryUsage);

        if ($writeHtml) {
            file_put_contents(Hyde::path('document-dump.html'), $html);
        }

        return $html;
    }

    #[NoReturn]
    public function dd(bool $writeHtml = true): void
    {
        $this->dump($writeHtml);

        dd($this->nodes);
    }

    protected function createStructureMapEntry(TestableHtmlElement $node, int $level): string
    {
        return sprintf("\n%s%s%s", str_repeat('    ', $level), $node->tag, $node->nodes->map(function (TestableHtmlElement $node) use ($level): string {
            return $this->createStructureMapEntry($node, $level + 1);
        })->implode(''));
    }

    protected function createTextMapEntry(TestableHtmlElement $node, bool $addNewline = true): string
    {
        $childEntries = $node->nodes->map(function (TestableHtmlElement $node): string {
            $isInline = in_array($node->tag, ['a', 'abbr', 'acronym', 'b', 'bdo', 'big', 'br', 'button', 'cite', 'code', 'dfn', 'em', 'i', 'img', 'input', 'kbd', 'label', 'map', 'object', 'q', 'samp', 'script', 'select', 'small', 'span', 'strong', 'sub', 'sup', 'textarea', 'time', 'tt', 'var']);

            return $this->createTextMapEntry($node, ! $isInline);
        })->implode('');

        return sprintf('%s%s%s', $addNewline ? "\n" : ' ', $node->text, $childEntries);
    }

    protected function createAstInspectionDump(): string
    {
        $html = '<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Document Dump</title><style>body { font-family: sans-serif; } .node { margin-left: 1em; }</style></head><body><h1>Document Dump</h1>';

        $html .= '<h2>Abstract Syntax Tree Node Inspection</h2>';
        $openAllButton = '<script>function openAll() {document.querySelectorAll(\'details\').forEach((el) => el.open = true);}</script><a href="javascript:openAll();" onclick="this.remove();">Open all</a>';
        $html .= sprintf("\n<details open><summary><strong>Document</strong> <small>$openAllButton</small></summary>\n<ul>%s</ul></details>\n", $this->nodes->map(function (TestableHtmlElement $node): string {
            return $this->createDumpNodeMapEntry($node);
        })->implode(''));

        $html .= '<section style="display: flex; flex-direction: row; flex-wrap: wrap; gap: 1em;">'.
            sprintf('<div><h2>Document Preview</h2><iframe src="data:text/html;base64,%s" width="960px" height="600px"></iframe></div>', base64_encode($this->html)).
            sprintf('<div><h2>Raw HTML</h2><textarea cols="120" rows="30" readonly style="width: 960px; height: 600px; white-space: pre; font-family: monospace;">%s</textarea></div>', e($this->html)).
        '</section>';

        $html .= '<h3>Node Structure</h3>';
        $html .= sprintf('<div style="max-width: 1440px; overflow-x: auto; border: 1px solid #333; padding: 0.5rem 1rem;"><pre><code>%s</code></pre></div>', $this->getStructure());

        $html .= '<h3>Text Representation</h3>';
        $html .= sprintf('<div style="max-width: 1440px; overflow-x: auto; border: 1px solid #333; padding: 0.5rem 1rem;"><pre><code>%s</code></pre></div>', $this->getTextRepresentation());

        $html .= '<hr>';

        $html .= '</body></html>';

        return $html;
    }

    protected function createDumpNodeMapEntry(TestableHtmlElement $node): string
    {
        $data = $node->toArray();

        $list = sprintf("\n    <ul class=\"node\">\n%s  </ul>\n", implode('', array_map(function (string|iterable $value, string $key): string {
            if ($value instanceof Collection) {
                if ($value->isEmpty()) {
                    return sprintf("      <li><strong>%s</strong>: <span>None</span></li>\n", ucfirst($key));
                }

                return sprintf("      <li><strong>%s</strong>: <ul>%s</ul></li>\n", ucfirst($key), $value->map(function (TestableHtmlElement $node): string {
                    return $this->createDumpNodeMapEntry($node);
                })->implode(''));
            }

            if (is_array($value)) {
                if (! is_numeric(array_key_first($value))) {
                    $value = array_map(function (string $value, string $key): string {
                        return sprintf('%s: %s', $key, str_contains($value, ' ') ? sprintf('"%s"', $value) : $value);
                    }, $value, array_keys($value));
                }
                $value = implode(', ', $value);
            }

            return sprintf("      <li><strong>%s</strong>: <span>%s</span></li>\n", ucfirst($key), $value);
        }, $data, array_keys($data))));

        if ($node->text) {
            $title = sprintf('<%s>%s</%s>', $node->tag, e($node->text), $node->tag);
        } else {
            $title = sprintf('<%s>', $node->tag);
        }

        return sprintf("  <li><%s><summary><strong>%s</strong></summary>%s  </details></li>\n", $node->tag === 'html' ? 'details open' : 'details', e($title), $list);
    }
}
