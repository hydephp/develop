<?php

declare(strict_types=1);

namespace Hyde\Framework\Factories\Concerns;

use Hyde\Framework\Factories\BlogPostDataFactory;
use Hyde\Framework\Factories\HydePageDataFactory;
use Hyde\Pages\MarkdownPost;

trait HasFactory
{
    public function constructFactoryData(PageDataFactory $data): void
    {
        foreach ($data->toArray() as $key => $value) {
            $this->{$key} = $value;
        }
    }

    protected function constructPageSchemas(): void
    {
        $this->constructPageSchema();

        if ($this instanceof MarkdownPost) {
            $this->constructFactoryData(new BlogPostDataFactory($this->matter, $this->markdown));
        }
    }

    protected function constructPageSchema(): void
    {
        $this->constructFactoryData(new HydePageDataFactory($this->matter, $this->markdown ?? false, $this::class, $this->identifier, $this->getOutputPath(), $this->routeKey));
    }
}
