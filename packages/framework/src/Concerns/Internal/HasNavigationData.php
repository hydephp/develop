<?php

namespace Hyde\Framework\Concerns\Internal;

use Hyde\Framework\Models\Pages\DocumentationPage;
use Hyde\Framework\Models\Pages\MarkdownPost;

trait HasNavigationData
{
    public function showInNavigation(): bool
    {
        return ! $this->navigation['hidden'];
    }

    public function navigationMenuPriority(): int
    {
        return $this->navigation['priority'];
    }

    public function navigationMenuLabel(): string
    {
        return $this->navigation['label'];
    }

    protected function constructNavigationData(): void
    {
        $this->setNavigationData(
            $this->findNavigationMenuLabel(),
            $this->findNavigationMenuHidden(),
            $this->findNavigationMenuPriority(),
        );
    }

    protected function setNavigationData(string $label, bool $hidden, int $priority): void
    {
        $this->navigation = [
            'label' => $label,
            'hidden' => $hidden,
            'priority' => $priority,
        ];
    }

    /**
     * Note that this also affects the documentation sidebar titles.
     */
    private function findNavigationMenuLabel(): string
    {
        if ($this->matter('navigation.label') !== null) {
            return $this->matter('navigation.label');
        }

        if (isset(static::getNavigationLabelConfig()[$this->routeKey])) {
            return static::getNavigationLabelConfig()[$this->routeKey];
        }

        return $this->matter('title') ?? $this->title;
    }

    private function findNavigationMenuHidden(): bool
    {
        if ($this instanceof MarkdownPost) {
            return true;
        }

        if ($this instanceof DocumentationPage) {
            return ! ($this->identifier === 'index' && ! in_array($this->routeKey, config('hyde.navigation.exclude', [])));
        }

        if ($this->matter('navigation.hidden', false)) {
            return true;
        }

        if (in_array($this->identifier, config('hyde.navigation.exclude', ['404']))) {
            return true;
        }

        return false;
    }

    private function findNavigationMenuPriority(): int
    {
        if ($this->matter('navigation.priority') !== null) {
            return $this->matter('navigation.priority');
        }

        if (array_key_exists($this->routeKey, config('hyde.navigation.order', []))) {
            return (int) config('hyde.navigation.order.'.$this->routeKey);
        }

        return 999;
    }

    private static function getNavigationLabelConfig(): array
    {
        return array_merge([
            'index' => 'Home',
            'docs/index' => 'Docs',
        ], config('hyde.navigation.labels', []));
    }
}
