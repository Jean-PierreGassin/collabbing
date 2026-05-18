<?php

namespace App\Http\Requests;

use App\Data\Users\UserProfileData;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UpdateUserProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $user = $this->route('user');
        $userId = null;

        if ($user instanceof User) {
            $userId = $user->id;
        }

        return [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($userId)],
            'bio' => ['nullable', 'string', 'max:500'],
            'password' => ['nullable', 'string', 'confirmed', 'max:128', Password::min(12)->letters()->numbers()],
        ];
    }

    public function toData(): UserProfileData
    {
        $validated = $this->validated();
        $bio = null;
        $password = null;

        if (array_key_exists('bio', $validated) && is_string($validated['bio'])) {
            $bio = $validated['bio'];
        }

        if (array_key_exists('password', $validated) && is_string($validated['password'])) {
            $password = $validated['password'];
        }

        return new UserProfileData(
            firstName: $this->string('first_name')->toString(),
            lastName: $this->string('last_name')->toString(),
            email: $this->string('email')->toString(),
            bio: $bio,
            password: $password
        );
    }
}
