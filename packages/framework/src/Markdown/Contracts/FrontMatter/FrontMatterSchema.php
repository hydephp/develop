<?php

declare(strict_types=1);

namespace Hyde\Markdown\Contracts\FrontMatter;

/**
 * Front matter schema interfaces are used by various Hyde components to
 * specify what data they provide or contain. They also serve as a
 * convenient way to see the supported front matter properties.
 *
 * Please note that while the array keys are directly connected to the supported
 * front matter properties, they are covered by the backwards compatibility
 * promise for HydePHP. However, the formats of the values are not.
 */
interface FrontMatterSchema
{
    //
}
