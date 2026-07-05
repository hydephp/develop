<?php

declare(strict_types=1);

namespace Hyde\Console\Helpers;

final class PublishablePage
{
    /**
     * @param  string  $key  The unique identifier for the page (e.g. 'posts').
     * @param  string  $label  The human-readable name shown in pickers (e.g. 'Posts feed').
     * @param  string  $description  A short help text describing the page.
     * @param  string  $source  The framework-relative path to the stub file, resolved via Hyde::vendorPath() when published.
     * @param  string|null  $defaultTarget  The default project-relative destination (e.g. '_pages/posts.blade.php'), or null when the page has no default and its destination must be resolved interactively or via --to.
     * @param  array<string, string>  $alternativeTargets  Additional valid destinations, mapping a project-relative path to a human label.
     * @param  bool  $allowCustomTarget  Whether the user may publish this page to a custom path.
     */
    public function __construct(
        public readonly string $key,
        public readonly string $label,
        public readonly string $description,
        public readonly string $source,
        public readonly ?string $defaultTarget,
        public readonly array $alternativeTargets = [],
        public readonly bool $allowCustomTarget = true,
    ) {
    }
}
