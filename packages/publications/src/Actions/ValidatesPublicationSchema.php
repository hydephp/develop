<?php

declare(strict_types=1);

namespace Hyde\Publications\Actions;

use Hyde\Facades\Filesystem;
use Hyde\Framework\Concerns\InvokableAction;
use Illuminate\Contracts\Validation\Validator;
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

    /** @var array<\Illuminate\Contracts\Validation\Validator> */
    protected array $fieldValidators;

    public function __construct(string $pubTypeName)
    {
        $this->schema = json_decode(Filesystem::getContents("$pubTypeName/schema.json"));
    }

    /** @return $this */
    public function __invoke(): static
    {
        $this->makePropertyValidator();
        $this->makeFieldsValidators();

        return $this;
    }

    /** @throws \Illuminate\Validation\ValidationException */
    public function validate(): void
    {
        $this->schemaValidator->validate();
        $this->validateFields();
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

        $input = $this->mapRulesInput($rules, $this->schema);

        $this->schemaValidator = validator($input, $rules);
    }

    protected function makeFieldsValidators(): void
    {
        $rules = [
            'type' => 'required|string',
            'name' => 'required|string',
            'rules' => 'nullable|array',
            'tagGroup' => 'nullable|string',
        ];

        if (is_array($this->schema->fields)) {
            foreach ($this->schema->fields as $field) {
                $input = $this->mapRulesInput($rules, $field);

                $this->fieldValidators[] = validator($input, $rules);
            }
        }
    }

    /** @return array<array-key, array<array-key, string>> */
    protected function evaluateFieldValidators(): array
    {
        return collect($this->fieldValidators)->map(function (Validator $validator): array {
            return $validator->errors()->all();
        })->all();
    }

    protected function validateFields(): void
    {
        foreach ($this->fieldValidators as $validator) {
            $validator->validate();
        }
    }

    protected function mapRulesInput(array $rules, stdClass $field): array
    {
        return collect($rules)->mapWithKeys(function (string $rule, string $key) use ($field): array {
            return [$key => $field->{$key} ?? null];
        })->all();
    }
}
