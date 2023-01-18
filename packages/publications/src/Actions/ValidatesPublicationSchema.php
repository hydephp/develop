<?php

declare(strict_types=1);

namespace Hyde\Publications\Actions;

use Hyde\Facades\Filesystem;
use Hyde\Framework\Concerns\InvokableAction;

use function json_decode;

/**
 * @see \Hyde\Publications\Testing\Feature\ValidatesPublicationSchemaTest
 */
class ValidatesPublicationSchema extends InvokableAction
{
    protected array $schema;
    protected bool $throw;

    public function __construct(string $pubTypeName, bool $throw = true)
    {
        $this->schema = json_decode(Filesystem::getContents("$pubTypeName/schema.json"));
        $this->throw = $throw;
    }

    public function __invoke()
    {
        // TODO: Implement __invoke() method.
    }
}
