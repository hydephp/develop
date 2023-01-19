<?php

declare(strict_types=1);

namespace Hyde\Publications\Actions;

use Hyde\Framework\Concerns\InvokableAction;
use Hyde\Markdown\Models\MarkdownDocument;
use Hyde\Publications\Models\PublicationFieldDefinition;
use Hyde\Publications\Models\PublicationType;
use Illuminate\Contracts\Validation\Validator;

use function array_flip;
use function array_merge;
use function in_array;
use function validator;

/**
 * @see \Hyde\Publications\Testing\Feature\PublicationPageValidatorTest
 */
class PublicationPageValidator extends InvokableAction
{
    protected PublicationType $publicationType;
    protected array $matter;

    protected Validator $validator;

    public function __construct(PublicationType $publicationType, string $pageIdentifier)
    {
        $this->publicationType = $publicationType;
        $this->matter = MarkdownDocument::parse("{$publicationType->getDirectory()}/$pageIdentifier.md")->matter()->toArray();
        unset($this->matter['__createdAt']);
    }

    /** @return $this */
    public function __invoke(): static
    {
        $rules = [];
        $input = [];

        foreach ($this->publicationType->getFields() as $field) {
            $rules[$field->name] = $this->getValidationRules($field);
            $input[$field->name] = $this->matter[$field->name] ?? null;
        }

        $this->validator = validator($input, $rules);

        return $this;
    }

    /** @throws \Illuminate\Validation\ValidationException */
    public function validate(): void
    {
        $this->validator->validate();
    }

    /** @return array<int, string> */
    public function errors(): array
    {
        return collect($this->validator->errors())->map(function (array $message): string {
            return implode(', ', $message);
        })->toArray();
    }

    public function warnings(): array
    {
        $warnings = [];

        $fields = $this->publicationType->getFields()->pluck('name')->toArray();
        foreach ($this->matter as $key => $value) {
            if (! in_array($key, $fields)) {
                $warnings[$key] = "This field is not defined in the schema.";
            }
        }

        return $warnings;
    }

    /** @deprecated Count the results instead */
    public function fields(): array
    {
        return $this->matter;
    }

    /** @return array<string, string> */
    public function getResults(): array
    {
        $results = [];
        $warnings = $this->warnings();
        $errors = $this->errors();

        $validatedFields = array_merge($this->matter, array_flip($this->publicationType->getFields()->pluck('name')->toArray()));

        foreach ($validatedFields as $key => $value) {
            if (isset($warnings[$key])) {
                $results[$key] = "Warning: $warnings[$key]";
            } elseif (isset($errors[$key])) {
                $results[$key] = "Error: $errors[$key]";
            } else {
                $results[$key] = 'Passed';
            }
        }

        return $results;
    }

    protected function getValidationRules(PublicationFieldDefinition $field): array
    {
        return (new PublicationFieldValidator($this->publicationType, $field))->getValidationRules();
    }
}
