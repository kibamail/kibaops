<?php

namespace App\Rules;

use App\Enums\CloudProviderType;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ImplementedCloudProviderType implements ValidationRule
{
    /**
     * Validate that the cloud provider type has been implemented in our system.
     * This rule checks if the provider type exists in the implemented list
     * and fails validation if the type is not yet supported.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $type = CloudProviderType::tryFrom($value);

        if ($type && !in_array($type, CloudProviderType::implemented())) {
            $fail("Cloud provider type '{$type->label()}' is not implemented yet.");
        }
    }
}
