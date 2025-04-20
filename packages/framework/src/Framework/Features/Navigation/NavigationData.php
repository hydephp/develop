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

    public readonly string $label;
    public readonly int $priority;
    public readonly bool $hidden;
    public readonly ?string $group;

    public function __construct(string $label, int $priority, bool $hidden, ?string $group = null)
    {
        $this->label = $label;
        $this->priority = $priority;
        $this->hidden = $hidden;
        $this->group = $group;

        parent::__construct($this->toArray());
    }

    /** @param  array{label: string, priority: int, hidden: bool, group: string|null}  $data */
    public static function make(array $data): self
    {
        return new self(...$data);
    }

    /** @return array{label: string,  priority: int, hidden: bool, group: string|null} */
    public function toArray(): array
    {
        return [
            'label' => $this->label,
            'priority' => $this->priority,
            'hidden' => $this->hidden,
            'group' => $this->group,
        ];
    }
}
