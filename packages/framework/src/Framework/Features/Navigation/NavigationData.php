<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Navigation;

use ArrayObject;
use Hyde\Markdown\Contracts\FrontMatter\SubSchemas\NavigationSchema;
use Hyde\Support\Concerns\JsonSerializesArrayable;
use Illuminate\Contracts\Support\Arrayable;
use JsonSerializable;

/**
 * Object implementation for the NavigationSchema. It extends the ArrayObject class so
 * that its data can be accessed using dot notation in the page's front matter data.
 */
final class NavigationData extends ArrayObject implements NavigationSchema, Arrayable, JsonSerializable
{
    use JsonSerializesArrayable;

    public ?string $label = null;
    public ?string $group = null;
    public ?bool $hidden = null;
    public ?int $priority = null;

    public function __construct(?string $label = null, ?string $group = null, ?bool $hidden = null, ?int $priority = null)
    {
        $this->label = $label;
        $this->group = $group;
        $this->hidden = $hidden;
        $this->priority = $priority;

        parent::__construct($this->toArray());
    }

    public static function make(array $data): self
    {
        return new self(
            $data['label'] ?? null,
            $data['group'] ?? null,
            $data['hidden'] ?? null,
            $data['priority'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'label' => $this->label,
            'group' => $this->group,
            'hidden' => $this->hidden,
            'priority' => $this->priority,
        ];
    }
}
