<?php

namespace Hyde\RealtimeCompiler\Actions;

use Hyde\RealtimeCompiler\Server;

/**
 * Hook into Hyde to compile a source file.
 */
class CompilesSourceFile
{
    /**
     * The source file to compile.
     * @var string
     */
    private string $path;

    /**
     * Construct a new Action instance.
     */
    public function __construct(string $path)
    {
        // Remove everything before the first underscore
        $this->path = substr($path, strpos($path, '_'));
    }

    /**
     * Trigger the compiler and return the compiled HTML string.
     * @return string
     */
    public function execute(): bool|string
    {
        Server::log('Compiler: Building page...');
        $output =  shell_exec('php '.HYDE_PATH.'/hyde rebuild '.$this->path.' --ansi');
        Server::log('Compiler: ' . $output);

        return $this->catchExceptions($output ?? 'No output received') ?:
            file_get_contents(HYDE_PATH . '/_site/'. $this->formatPathname());
    }

    /**
     * Convert the source path string to the output path string.
     * @return string
     */
    private function formatPathname(): string
    {
        $filename = $this->path;

        $filename = str_replace('_pages', '', $filename);
        $filename = str_replace('_', '', $filename);
        $filename = str_replace('.blade.php', '.html', $filename);
        $filename = str_replace('.md', '.html', $filename);

        return $filename;
    }

    /**
     * (Try to) Catch any exceptions, otherwise return false if it's safe to proceed.
     */
    private function catchExceptions(string $output): string|false
    {
        // Might be too general, can always add an array of FQSN exceptions
        if (strpos($output, 'Exception') !== false) {
            Server::log("Error: \033[0;31mException detected in output.\033[0m\n");
            return $this->formatErrorHTML($output);
        }

        return false;
    }

    private function formatErrorHTML(string $output): string
    {
        $dictionary = array(
            // Replace ANSI colors with HTML
            "\033[33m" => '<span style="color:gold">',
            "\033[32m" => '<span style="color:green">',
            "\033[37m" => '<span style="color:lightgray">',
            "\033[39;1m" => '<span style="color:white">',
            "\033[41;1m" => '<span style="color:white;background-color:red;font-size:120%;">',
            "\033[39m"   => '</span>' ,
            "\033[39;22m"   => '</span>' ,
            "\033[49;22m"   => '</span>' ,

            // Basic syntax highlighting
            '&lt;?php' => '<span style="color:lightblue">&lt;?php</span>',
            '?&gt;' => '<span style="color:lightblue">?&gt;</span>',
            'echo' => '<span style="color:lightpink">echo</span>',
            '/&gt;' => '<span style="color:lightpink">/&gt;</span>',
            '➜' => '<span style="color:orange">➜</span>',
            '▕' => '<span style="color:lightgray">▕</span>',
            '/**' => '<span style="color:gray">/**</span><span style="color:lightgray">',
            '**/' => '</span><span style="color:gray">**/</span>',
            '//' => '<span style="color:gray">//</span>',
            '\\' => '<span style="opacity:0.75">\</span>',
            '$' => '<span style="color:lightgreen">$</span>',
            '(' => '<span style="color:gray">(</span>',
            ')' => '<span style="color:gray">)</span>',
                

        );
        $output = str_replace(array_keys($dictionary), $dictionary, e($output));

        return '<!DOCTYPE html><html lang="en"><head><title>An exception has been detected</title></head><body>'
        .'<h1>Error: Exception detected in output.</h1>'
        ."\n".'<p style="font-size:20px;">Please report any issues and/or feedback about this error page at GitHub <a href="https://github.com/hydephp/realtime-compiler/issues/3">https://github.com/hydephp/realtime-compiler/issues/3</a>!</p>'
        ."\n\n".'<pre style="background:black;color:white;font-size:14px;font-family:monospace;padding:16px;width:fit-content;">'.($output).'</pre>'
        .'</body></html>';
    }
}