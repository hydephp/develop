<?php

namespace Hyde\Framework\Models\Support;

use Hyde\Framework\Modules\Metadata\GlobalMetadataBag;

/**
 * Object representation for the HydePHP site.
 *
 * @see \Hyde\Framework\Testing\Feature\SiteTest
 */
final class Site
{
    /** @var string The relative path to the output directory */
    public static string $outputPath;

    public ?string $url;
    public ?string $name;
    public ?string $language;

    public function __construct()
    {
        $this->url = self::url();
        $this->name = self::name();
        $this->language = self::language();
    }

    public static function url(): ?string
    {
        return config('site.url');
    }

    public static function name(): ?string
    {
        return config('site.name');
    }

    public static function language(): ?string
    {
        return config('site.language');
    }

    public static function metadata(): GlobalMetadataBag
    {
        return GlobalMetadataBag::make();
    }
}
