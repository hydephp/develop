<?php

declare(strict_types=1);

namespace Hyde\Console\Helpers;

/**
 * An immutable value object describing a starter/default page that can be published into the project's _pages directory.
 *
 * Unlike view groups, a page may have multiple valid destinations and carries display metadata, so each publishable
 * page is modelled explicitly and registered in the {@see PublishablePages} registry rather than as a fixed file map.
 *
 * @see \Hyde\Console\Helpers\PublishablePages
 */
final class PublishablePage
{
    /** @param  array<string, string>  $alternativeTargets */
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
