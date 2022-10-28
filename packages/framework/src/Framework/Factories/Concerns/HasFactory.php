<?php

declare(strict_types=1);

namespace Hyde\Framework\Factories\Concerns;

trait HasFactory
{
    public function constructFactoryData(PageDataFactory $data): void
    {
        foreach ($data->toArray() as $key => $value) {
            $this->{$key} = $value;
        }
    }
}
