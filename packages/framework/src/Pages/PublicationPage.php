<?php

declare(strict_types=1);

namespace Hyde\Pages;

use Hyde\Framework\Features\Publications\Models\PublicationType;
use Hyde\Markdown\Models\FrontMatter;

/**
 * Publication pages adds an easy way to create custom no-code page types,
 * with support using a custom front matter schema and Blade templates.
 */
class PublicationPage extends Concerns\HydePage
{
    public PublicationType $type;

    public function __construct(PublicationType $type, string $identifier = '', FrontMatter|array $matter = [])
    {
        $this->type = $type;

        parent::__construct($identifier, $matter);
    }

    public function compile(): string
    {
        // TODO: Implement compile() method.
    }
}
