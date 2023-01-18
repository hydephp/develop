<?php

declare(strict_types=1);

namespace Hyde\Publications\Actions;

use Hyde\Facades\Filesystem;
use Hyde\Framework\Concerns\InvokableAction;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Support\Collection;
use stdClass;

use function json_decode;
use function validator;

/**
 * @see \Hyde\Publications\Testing\Feature\ValidatesPublicationSchemaTest
 */
class ValidatesPublicationSchema extends InvokableAction
{
    protected stdClass $schema;

    protected Validator $schemaValidator;
    protected Collection $fieldValidators;

    public function __construct(string $pubTypeName)
    {
        $this->schema = json_decode(Filesystem::getContents("$pubTypeName/schema.json"));
        $this->fieldValidators = new Collection();
    }

    /** @return $this */
    public function __invoke(): static
    {
        $schema = $this->schema;

        $this->schemaValidator = validator([
            'name' => $schema->name ?? null,
            'canonicalField' => $schema->canonicalField ?? null,
            'detailTemplate' => $schema->detailTemplate ?? null,
            'listTemplate' => $schema->listTemplate ?? null,
            'sortField' => $schema->sortField ?? null,
            'sortAscending' => $schema->sortAscending ?? null,
            'pageSize' => $schema->pageSize ?? null,
            'fields' => $schema->fields ?? null,
            'directory' => $schema->directory ?? null,
        ], [
            'name' => 'required|string',
            'canonicalField' => 'nullable|string',
            'detailTemplate' => 'nullable|string',
            'listTemplate' => 'nullable|string',
            'sortField' => 'nullable|string',
            'sortAscending' => 'nullable|boolean',
            'pageSize' => 'nullable|integer',
            'fields' => 'nullable|array',
            'directory' => 'nullable|prohibited',
        ]);

        // TODO warn if fields are empty?

        // TODO warn if canonicalField does not match meta field or actual?

        // TODO Warn if template files do not exist (assuming files not vendor views)?

        // TODO warn if pageSize is less than 0 (as that equals no pagination)?

        foreach ($schema->fields as $field) {
            // TODO check tag group exists?

            $this->fieldValidators->add(validator([
                'type' => $field->type ?? null,
                'name' => $field->name ?? null,
                'rules' => $field->rules ?? null,
                'tagGroup' => $field->tagGroup ?? null,
            ], [
                'type' => 'required|string',
                'name' => 'required|string',
                'rules' => 'nullable|array',
                'tagGroup' => 'nullable|string',
            ]));
        }

        return $this;
    }

    public function validate(): void
    {
        $this->schemaValidator->validate();
        $this->fieldValidators->each(fn (Validator $validator): array => $validator->validate());
    }

    public function errors(): array
    {
        return [
            'schema' => $this->schemaValidator->errors()->toArray(),
            'fields' => $this->fieldValidators->map(fn(Validator $validator): array => $validator->errors()->toArray())->toArray(),
        ];
    }
}
