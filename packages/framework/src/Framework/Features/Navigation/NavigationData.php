<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Navigation;

use ArrayObject;
use Hyde\Markdown\Contracts\FrontMatter\SubSchemas\NavigationSchema;
use Hyde\Support\Concerns\Serializable;
use Hyde\Support\Contracts\SerializableContract;

/**
 * Object implementation for the NavigationSchema. It extends the ArrayObject class so
 * that its data can be accessed using dot notation in the page's front matter data.
 */
final class NavigationData extends ArrayObject implements NavigationSchema, SerializableContract
{
    use Serializable;

    public readonly ?string $label;
    public readonly ?string $group;
    public readonly ?bool $hidden;
    public readonly ?int $priority;

    public function __construct(?string $label = null, ?string $group = null, ?bool $hidden = null, ?int $priority = null)
    {
        $this->label = $label;
        $this->group = $group;
        $this->hidden = $hidden;
        $this->priority = $priority;

        parent::__construct($this->toArray());
    }

    /** @param  array{label: string|null, group: string|null, hidden: bool|null, priority: int|null}  $data */
    public static function make(array $data): self
    {
        return new self(
            $data['label'] ?? null,
            $data['group'] ?? null,
            $data['hidden'] ?? null,
            $data['priority'] ?? null,
        );
    }

    /** @return array{label: string|null, group: string|null, hidden: bool|null, priority: int|null} */
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
