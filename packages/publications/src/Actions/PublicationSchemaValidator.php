<?php

declare(strict_types=1);

namespace Hyde\Publications\Actions;

use stdClass;
use Hyde\Facades\Filesystem;
use Illuminate\Contracts\Validation\Validator;

use function collect;
use function is_array;
use function validator;
use function json_decode;

/**
 * @see \Hyde\Publications\Testing\Feature\PublicationSchemaValidatorTest
 */
class PublicationSchemaValidator
{
    protected stdClass $schema;

    protected Validator $schemaValidator;
    protected array $fieldValidators = [];

    public static function call(string $publicationTypeName): static
    {
        return (new self($publicationTypeName))->__invoke();
    }

    public function __construct(string $publicationTypeName)
    {
        $this->schema = json_decode(Filesystem::getContents("$publicationTypeName/schema.json"));
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

        $this->schemaValidator = $this->makeValidator($rules, $this->schema);
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
                $this->fieldValidators[] = $this->makeValidator($rules, $field);
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

    protected function makeValidator(array $rules, stdClass $input): Validator
    {
        return validator($this->mapRulesInput($rules, $input), $rules);
    }

    protected function mapRulesInput(array $rules, stdClass $input): array
    {
        return collect($rules)->mapWithKeys(function (string $rule, string $key) use ($input): array {
            return [$key => $input->{$key} ?? null];
        })->all();
    }
}
