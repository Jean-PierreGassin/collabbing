<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class RegisterUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'username' => ['required', 'string', 'min:3', 'max:20', 'regex:/^[A-Za-z0-9_-]+$/', 'unique:users,username'],
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'confirmed', 'max:128', Password::min(12)->letters()->numbers()],
        ];
    }

    protected function prepareForValidation(): void
    {
        $email = $this->input('email');
        $firstName = $this->input('first_name');
        $lastName = $this->input('last_name');

        if (is_string($email)) {
            $email = Str::lower(Str::squish($email));
        }

        if (is_string($firstName)) {
            $firstName = Str::squish($firstName);
        }

        if (is_string($lastName)) {
            $lastName = Str::squish($lastName);
        }

        $this->merge([
            'email' => $email,
            'first_name' => $firstName,
            'last_name' => $lastName,
        ]);
    }
}
