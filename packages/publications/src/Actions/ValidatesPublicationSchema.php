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

        $rules = [
            'type' => 'required|string',
            'name' => 'required|string',
            'rules' => 'nullable|array',
            'tagGroup' => 'nullable|string',
        ];

        if (is_array($schema->fields)) {
            foreach ($schema->fields as $field) {
                foreach ($rules as $key => $rule) {
                    $input[$key] = $field->{$key} ?? null;
                }

                $this->fieldValidators->add(validator($input, $rules));
            }
        }
    }

    /** @return array<array-key, array<array-key, string>> */
    protected function evaluateFieldValidators(): array
    {
        return $this->fieldValidators->map(function (Validator $validator): array {
            return $validator->errors()->all();
        })->all();
    }
}
