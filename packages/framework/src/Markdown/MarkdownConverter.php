<?php

declare(strict_types=1);

namespace Hyde\Markdown;

use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;

/**
 * The base Markdown converter class.
 *
 * "Extends" \League\CommonMark\CommonMarkConverter.
 */
class MarkdownConverter extends \League\CommonMark\MarkdownConverter
{
    /**
     * Create a new Markdown converter pre-configured for CommonMark.
     *
     * @param  array<string, mixed>  $config
     */
    public function __construct(array $config = [])
    {
        $environment = new Environment($config);
        $environment->addExtension(new CommonMarkCoreExtension());

        parent::__construct($environment);
    }
}
