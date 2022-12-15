<?php

declare(strict_types=1);

namespace Hyde\Framework\Actions;

use Hyde\Framework\Concerns\InvokableAction;
use Hyde\Hyde;
use Illuminate\Support\Facades\Blade;
use InvalidArgumentException;

use function file_exists;
use function file_get_contents;
use function sprintf;


/**
 * Compile any Blade file using the Blade facade as it allows us to render
 * it without having to register the directory with the view finder.
 */
class AnonymousViewCompiler extends InvokableAction
{
    protected string $viewPath;
    protected array $data;

    public function __construct(string $viewPath, array $data = [])
    {
        $this->viewPath = $viewPath;
        $this->data = $data;
    }

    public function __invoke(): string
    {
        if (! file_exists(Hyde::path($this->viewPath))) {
            throw new InvalidArgumentException(sprintf('View [%s] not found.', $this->viewPath));
        }

        return Blade::render(
            file_get_contents(Hyde::path($this->viewPath)),
            $this->data
        );
    }
}
