<?php

namespace App\Http\Requests\User;

use App\Rules\UserRoleRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'role' => ['required', new UserRoleRule()],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ];
    }
}

