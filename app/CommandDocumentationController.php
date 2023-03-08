<?php

declare(strict_types=1);

namespace App;

use Hyde\Markdown\Models\Markdown;
use Hyde\Pages\DocumentationPage;
use Illuminate\Support\Facades\Blade;
use function file_get_contents;
use function implode;
use function json_decode;
use function max;
use function str_pad;
use function str_replace;
use function strip_tags;
use function strlen;

/** @internal // FIXME Remove from monorepo */
class CommandDocumentationController
{
    protected array $commands;
    protected int $lengthOfLongestArgument;

    public function __construct()
    {
        $commands = file_get_contents(DocumentationPage::path('_data/commands.json'));
        $commands = json_decode($commands, false)->commands;
        $this->commands = $commands;
    }

    public function __invoke(): string
    {
        $commands = $this->commands;
        $this->lengthOfLongestArgument = $this->getLengthOfLongestArgument($commands);

        $rendered = [];
        $compiled = [];

        $blade = file_get_contents(DocumentationPage::path('_data/partials/command.blade.php'));

        foreach ($commands as $command) {
            $rendered[] = Blade::render($blade, ['command' => $command, 'controller' => $this]);
        }

        foreach ($rendered as $render) {
            $compiled[] = Markdown::render($render, DocumentationPage::class);
        }

        $html = implode("\n", $compiled);

        $search = '<span style="color: #FFCB6B;">php</span>';
        $marker = '<span style="color:#3A3F58; text-align: right; -webkit-user-select: none; user-select: none;" class="line-number">$</span>';

        return str_replace($search, $marker.$search, $html);
    }

    /**
     * @param array{
     *     name: string, shortcut: string, accept_value: bool,
     *      is_value_required: bool, is_multiple: bool,
     *     description: string, default: bool } $option
     */
    public function formatOption(array $option, object $command): string
    {
        $name = $option['name'];
        $name = str_pad($name, $this->getLengthOfLongestOption($command) + 2);

        $description = $option['description'];

        return "{$name}".strip_tags($description);
    }

    private function getLengthOfLongestArgument(array $commands)
    {
        $length = 0;

        foreach ($commands as $command) {
            foreach ($command->definition->options as $argument) {
                $length = max($length, strlen($argument->name));
            }
            foreach ($command->definition->arguments as $argument) {
                $length = max($length, strlen($argument->name));
            }
        }

        return $length;
    }

    private function getLengthOfLongestOption(object $command)
    {
        $length = 0;
        foreach ($command->definition->options as $argument) {
            $length = max($length, strlen($argument->name));
        }
        foreach ($command->definition->arguments as $argument) {
            $length = max($length, strlen($argument->name));
        }

        return $length;
    }
}
