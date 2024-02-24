<?php

declare(strict_types=1);

namespace Hyde\Testing\Support;

class TestView extends \Illuminate\Testing\TestView
{
    /**
     * Assert that the given HTML is contained within the view.
     *
     * @return $this
     */
    public function assertSeeHtml(string $value): static
    {
        return $this->assertSee($value, false);
    }
}
