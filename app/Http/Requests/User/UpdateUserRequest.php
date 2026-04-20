<?php

namespace App\Http\Requests\User;

use App\Models\User;
use App\Rules\UserRoleRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        /** @var \App\Models\User|null $user */
        $user = $this->route('user');

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                Rule::unique('users', 'email')->ignore($user instanceof User ? $user->id : null),
            ],
            'role' => ['required', new UserRoleRule()],
            'password' => ['nullable', 'string', 'min:6', 'confirmed'],
        ];
    }
}

