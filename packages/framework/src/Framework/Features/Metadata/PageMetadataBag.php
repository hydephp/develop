<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Metadata;

use Hyde\Facades\Meta;
use Hyde\Pages\Concerns\HydePage;
use Hyde\Pages\MarkdownPost;
use Hyde\Foundation\Kernel\Hyperlinks;
use Hyde\Support\Facades\Render;

use function substr_count;
use function str_repeat;
use function str_starts_with;

class PageMetadataBag extends MetadataBag
{
    protected HydePage $page;
    protected bool $generated = false;
    protected bool $generating = false;

    public function __construct(HydePage $page)
    {
        $this->page = $page;
    }

    public function get(): array
    {
        $this->generateIfNeeded();

        return parent::get();
    }

    public function add(MetadataElementContract|string $element): static
    {
        $this->generateIfNeeded();

        return parent::add($element);
    }

    protected function generateIfNeeded(): void
    {
        if ($this->generated || $this->generating) {
            return;
        }

        $this->generating = true;

        try {
            $this->addDynamicPageMetadata($this->page);
            $this->generated = true;
        } finally {
            $this->generating = false;
        }
    }

    protected function addDynamicPageMetadata(HydePage $page): void
    {
        if ($page->getCanonicalUrl()) {
            $this->add(Meta::link('canonical', $page->getCanonicalUrl()));
        }

        if ($page->has('description')) {
            $this->add(Meta::name('description', $page->data('description')));
            $this->add(Meta::property('description', $page->data('description')));
        }

        if ($page->has('title')) {
            $this->add(Meta::name('twitter:title', $page->title()));
            $this->add(Meta::property('title', $page->title()));
        }

        if ($page instanceof MarkdownPost) {
            $this->addMetadataForMarkdownPost($page);
        }
    }

    protected function addMetadataForMarkdownPost(MarkdownPost $page): void
    {
        $this->addPostMetadataIfExists($page, 'author');
        $this->addPostMetadataIfExists($page, 'category', 'keywords');

        if ($page->getCanonicalUrl()) {
            $this->add(Meta::name('url', $page->getCanonicalUrl()));
            $this->add(Meta::property('url', $page->getCanonicalUrl()));
        }

        if ($page->has('date')) {
            $this->add(Meta::property('og:article:published_time', $page->date->datetime));
        }

        if ($page->has('image')) {
            $this->add(Meta::property('image', $this->resolveImageLink((string) $page->data('image'))));
        }

        $this->add(Meta::property('type', 'article'));
    }

    protected function addPostMetadataIfExists(MarkdownPost $page, string $property, ?string $name = null): void
    {
        if ($page->has($property)) {
            $this->add(Meta::name($name ?? $property, (string) $page->data($property)));
        }
    }

    protected function resolveImageLink(string $image): string
    {
        // FeaturedImage resolves local assets against the active render route. Preserve
        // an already-relative result instead of applying page traversal a second time.
        if (Hyperlinks::isRemote($image) || str_starts_with($image, '../')) {
            return $image;
        }

        return $this->calculatePathTraversal().$image;
    }

    private function calculatePathTraversal(): string
    {
        $routeKey = Render::getPage() === $this->page ? Render::getRouteKey() : null;

        if ($routeKey !== null) {
            return str_repeat('../', substr_count($routeKey, '/'));
        }

        $depth = substr_count($this->page->getOutputPath(), '/');

        // Empty post identifiers historically resolve relative to the post output directory.
        return str_repeat('../', $depth + (int) ($this->page->identifier === ''));
    }
}
