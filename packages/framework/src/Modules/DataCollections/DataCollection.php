<?php

namespace Hyde\Framework\Modules\DataCollections;

use Hyde\Framework\Hyde;
use Illuminate\Support\Collection;

class DataCollection extends Collection
{
    public string $key;
    public string $name;

    protected float $timeStart;
    public float $parseTimeInMs;

    public static string $sourceDirectory = '_data';

    public function __construct(string $key, ?string $name = null)
    {
        $this->timeStart = microtime(true);
        $this->key = $key;
        $this->name = $name ?? Hyde::titleFromSlug($key);

        parent::__construct();
    }

    public function push(...$values)
    {
        $this->parseTimeInMs = round((microtime(true) - $this->timeStart) * 1000, 2);

        return parent::push($values);
    }
}
