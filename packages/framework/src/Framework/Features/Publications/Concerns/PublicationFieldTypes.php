<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Publications\Concerns;

/**
 * The supported field types for publication types.
 *
 * @see \Hyde\Framework\Features\Publications\Models\PublicationFieldType
 * @see \Hyde\Framework\Testing\Feature\PublicationFieldTypesEnumTest
 */
enum PublicationFieldTypes
{
  case String;
  case Boolean;
  case Integer;
  case Float;
  case Datetime;
  case Url;
  case Array;
  case Text;
  case Image;
  case Tag;
}
