<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class UserRoleRule implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $role = $this->normalize($value);

        if (!in_array($role, ['administrator', 'staff'], true)) {
            $fail('Vai trò không hợp lệ.');
        }
    }

    public static function normalize(mixed $value): string
    {
        return strtolower(trim((string) $value));
    }
}

