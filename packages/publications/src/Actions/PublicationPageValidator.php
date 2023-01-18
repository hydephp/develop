<?php

declare(strict_types=1);

namespace Hyde\Publications\Actions;

use Hyde\Framework\Concerns\InvokableAction;
use Hyde\Markdown\Models\MarkdownDocument;
use Hyde\Publications\Models\PublicationType;

use Illuminate\Contracts\Validation\Validator;

use function collect;
use function validator;

/**
 * @see \Hyde\Publications\Testing\Feature\PublicationPageValidatorTest
 */
class PublicationPageValidator extends InvokableAction
{
    protected PublicationType $publicationType;
    protected array $matter;

    protected array $fieldValidators = [];

    public function __construct(PublicationType $publicationType, string $pageIdentifier)
    {
        $this->publicationType = $publicationType;
        $this->matter = MarkdownDocument::parse("{$publicationType->getDirectory()}/$pageIdentifier.md")->matter()->toArray();
    }

    /** @return $this */
    public function __invoke(): static
    {
        $rules = [];
        $input = [];

        foreach ($this->publicationType->getFields() as $field) {
            $validator = new PublicationFieldValidator($this->publicationType, $field);
            $rules[$field->name] = $validator->getValidationRules();
            $input[$field->name] = $this->matter[$field->name] ?? null;
        }

        $this->fieldValidators[] = validator($input, $rules);

        return $this;
    }

    /** @throws \Illuminate\Validation\ValidationException */
    public function validate(): void
    {
        foreach ($this->fieldValidators as $validator) {
            $validator->validate();
        }
    }

    /** @return array<string, array */
    public function errors(): array
    {
        return collect($this->fieldValidators)->map(function (Validator $validator): array {
            return $validator->errors()->all();
        })->all();
    }
}
