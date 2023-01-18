<?php

declare(strict_types=1);

namespace Hyde\Publications\Actions;

use Hyde\Facades\Filesystem;
use Hyde\Framework\Concerns\InvokableAction;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Support\Collection;
use function json_decode;
use stdClass;
use function validator;

/**
 * @see \Hyde\Publications\Testing\Feature\ValidatesPublicationSchemaTest
 */
class ValidatesPublicationSchema extends InvokableAction
{
    protected stdClass $schema;

    protected Validator $schemaValidator;

    /** @var \Illuminate\Support\Collection<\Illuminate\Contracts\Validation\Validator> */
    protected Collection $fieldValidators;

    public function __construct(string $pubTypeName)
    {
        $this->schema = json_decode(Filesystem::getContents("$pubTypeName/schema.json"));
        $this->fieldValidators = new Collection();
    }

    /** @return $this */
    public function __invoke(): static
    {
        $this->makePropertyValidator();

        $this->makeFieldsValidator();

        // TODO warn if fields are empty?

        // TODO warn if canonicalField does not match meta field or actual?

        // TODO Warn if template files do not exist (assuming files not vendor views)?

        // TODO warn if pageSize is less than 0 (as that equals no pagination)?

        return $this;
    }

    /** @throws \Illuminate\Validation\ValidationException */
    public function validate(): void
    {
        $this->schemaValidator->validate();
        $this->fieldValidators->each(fn (Validator $validator): array => $validator->validate());
    }

    /** @return array<string, Validator|array */
    public function errors(): array
    {
        return [
            'schema' => $this->schemaValidator->errors()->all(),
            'fields' => $this->evaluateFieldValidators(),
        ];
    }

    protected function makePropertyValidator(): void
    {
        $rules = [
            'name' => 'required|string',
            'canonicalField' => 'nullable|string',
            'detailTemplate' => 'nullable|string',
            'listTemplate' => 'nullable|string',
            'sortField' => 'nullable|string',
            'sortAscending' => 'nullable|boolean',
            'pageSize' => 'nullable|integer',
            'fields' => 'nullable|array',
            'directory' => 'nullable|prohibited',
        ];

        foreach ($rules as $key => $rule) {
            $input[$key] = $this->schema->{$key} ?? null;
        }

        $this->schemaValidator = validator($input, $rules);
    }

    protected function makeFieldsValidator(): void
    {
        $schema = $this->schema;

        if (is_array($schema->fields)) {
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
        }
    }

    /** @return string[][] */
    protected function evaluateFieldValidators(): array
    {
        return $this->fieldValidators->map(function (Validator $validator): array {
            return $validator->errors()->all();
        })->all();
    }
}
