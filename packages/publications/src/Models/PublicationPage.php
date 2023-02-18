<?php

declare(strict_types=1);

namespace Hyde\Publications\Models;

use Hyde\Pages\Concerns\BaseMarkdownPage;
use Hyde\Publications\Actions\PublicationPageCompiler;

/**
 * Publication pages adds an easy way to create custom no-code page types,
 * with support using a custom front matter schema and Blade templates.
 *
 * @see \Hyde\Publications\Testing\Feature\PublicationPageTest
 */
class PublicationPage extends BaseMarkdownPage
{
    /** @var string ($publicationType->identifier) */
    public static string $publicationType;

    /** @var string ($publicationType->identifier) */
    public static string $sourceDirectory;

    /** @var string ($publicationType->identifier) */
    public static string $outputDirectory;

    /** @var string ($publicationType->detail) */
    public static string $template;

    public static function getPublicationType(): PublicationType
    {
        return PublicationType::get(static::$publicationType);
    }

    public static function sourceDirectory(): string
    {
        return static::getPublicationType()->getIdentifier();
    }

    public static function outputDirectory(): string
    {
        return static::getPublicationType()->getIdentifier();
    }

    public static function getTemplate(): string
    {
        return static::getPublicationType()->detailTemplate;
    }

    public function getBladeView(): string
    {
        return static::getPublicationType()->detailTemplate;
    }

    public function compile(): string
    {
        return PublicationPageCompiler::call($this);
    }
}
