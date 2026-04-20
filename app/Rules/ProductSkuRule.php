<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ProductSkuRule implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $sku = trim((string) $value);

        if ($sku === '' || strlen($sku) < 2 || strlen($sku) > 64) {
            $fail('SKU must be between 2 and 64 characters.');
            return;
        }

        if (!preg_match('/^[A-Za-z0-9][A-Za-z0-9_.-]*$/', $sku)) {
            $fail('SKU can only contain letters, numbers, dot, underscore, and dash.');
        }
    }
}

