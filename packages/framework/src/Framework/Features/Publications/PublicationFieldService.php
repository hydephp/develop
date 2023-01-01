<?php

declare(strict_types=1);

namespace Hyde\Framework\Features\Publications;

use Hyde\Framework\Features\Publications\Models\PublicationFields\ArrayField;
use Hyde\Framework\Features\Publications\Models\PublicationFields\BooleanField;
use Hyde\Framework\Features\Publications\Models\PublicationFields\DatetimeField;
use Hyde\Framework\Features\Publications\Models\PublicationFields\FloatField;
use Hyde\Framework\Features\Publications\Models\PublicationFields\ImageField;
use Hyde\Framework\Features\Publications\Models\PublicationFields\IntegerField;
use Hyde\Framework\Features\Publications\Models\PublicationFields\StringField;
use Hyde\Framework\Features\Publications\Models\PublicationFields\TagField;
use Hyde\Framework\Features\Publications\Models\PublicationFields\TextField;
use Hyde\Framework\Features\Publications\Models\PublicationFields\UrlField;
use Hyde\Framework\Features\Publications\Models\PublicationType;

/**
 * @see  \Hyde\Framework\Features\Publications\Models\PublicationFields\StringField
 * @see  \Hyde\Framework\Features\Publications\Models\PublicationFields\DatetimeField
 * @see  \Hyde\Framework\Features\Publications\Models\PublicationFields\BooleanField
 * @see  \Hyde\Framework\Features\Publications\Models\PublicationFields\IntegerField
 * @see  \Hyde\Framework\Features\Publications\Models\PublicationFields\FloatField
 * @see  \Hyde\Framework\Features\Publications\Models\PublicationFields\ImageField
 * @see  \Hyde\Framework\Features\Publications\Models\PublicationFields\ArrayField
 * @see  \Hyde\Framework\Features\Publications\Models\PublicationFields\TextField
 * @see  \Hyde\Framework\Features\Publications\Models\PublicationFields\UrlField
 * @see  \Hyde\Framework\Features\Publications\Models\PublicationFields\TagField
 */
class PublicationFieldService
{
    public static function getDefaultValidationRulesForFieldType(PublicationFieldTypes $fieldType): array
    {
        //
    }

    public static function getValidationRulesForPublicationFieldEntry(PublicationType $publicationType, string $fieldName): array
    {
        //
    }
}
