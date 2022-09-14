<?php

namespace Hyde\Framework\Models\Pages;

use Hyde\Framework\Concerns\HydePage;

class HtmlPage extends HydePage
{
    public function compile(): string
    {
        return file_get_contents($this->getSourcePath());
    }
}