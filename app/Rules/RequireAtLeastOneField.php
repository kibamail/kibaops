<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class RequireAtLeastOneField implements ValidationRule
{
    /**
     * Create a new validation rule that requires at least one of the specified fields.
     */
    public function __construct(
        private array $fields
    ) {}

    /**
     * Validate that at least one of the specified fields is present in the request.
     * This rule is useful for update operations where multiple fields are optional
     * but at least one must be provided to perform a meaningful update.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $request = request();

        $hasAtLeastOne = false;
        foreach ($this->fields as $field) {
            if ($request->has($field) && $request->input($field) !== null && $request->input($field) !== '') {
                $hasAtLeastOne = true;
                break;
            }
        }

        if (! $hasAtLeastOne) {
            $fieldList = implode(' or ', $this->fields);
            $fail("At least one of the following fields is required: {$fieldList}.");
        }
    }
}
