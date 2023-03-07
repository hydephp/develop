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
 * Furthermore, as interface implementations are just a guideline, a class may
 * stop implementing an interface at any time, so you should not use
 * the schema interfaces for type-hinting.
 *
 * Also note that if a root property value is an array, it means that either one of
 * the types are supported. If a type within that array is an array, it means that
 * the property is a sub-schema, which is an array of the supported types.
 *
 * All front matter properties are optional, unless otherwise specified.
 */
interface FrontMatterSchema
{
    //
}
