<?php

declare(strict_types=1);

namespace Hyde\Publications\Actions;

use function array_flip;
use function array_merge;

use Hyde\Markdown\Models\MarkdownDocument;
use Hyde\Publications\Models\PublicationFieldDefinition;
use Hyde\Publications\Models\PublicationType;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Support\Str;

use function in_array;
use function lcfirst;
use function sprintf;
use function validator;

/**
 * @see \Hyde\Publications\Testing\Feature\PublicationPageValidatorTest
 */
class PublicationPageValidator
{
    protected PublicationType $publicationType;
    protected array $matter;

    protected Validator $validator;

    public static function call(PublicationType $publicationType, string $pageIdentifier): static
    {
        return (new self($publicationType, $pageIdentifier))->__invoke();
    }

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
                $warnings[$key] = sprintf('The %s field is not defined in the publication type.', lcfirst(Str::title($key)));
            }
        }

        return $warnings;
    }

    public function validatedFields(): array
    {
        return array_merge($this->matter, array_flip($this->publicationType->getFields()->pluck('name')->toArray()));
    }

    /** @return array<string, string> */
    public function getResults(): array
    {
        $results = [];
        $warnings = $this->warnings();
        $errors = $this->errors();

        foreach ($this->validatedFields() as $key => $value) {
            if (isset($warnings[$key])) {
                $results[$key] = "Warning: $warnings[$key]";
            } elseif (isset($errors[$key])) {
                $results[$key] = "Error: $errors[$key]";
            } else {
                $results[$key] = "Field $key passed.";
            }
        }

        return $results;
    }

    protected function getValidationRules(PublicationFieldDefinition $field): array
    {
        return (new PublicationFieldValidator($this->publicationType, $field))->getValidationRules();
    }
}
