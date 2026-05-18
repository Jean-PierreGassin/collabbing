<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class SearchIdeas extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:80'],
        ];
    }

    public function searchTerm(): ?string
    {
        $validated = $this->validated();
        $search = $validated['search'] ?? null;

        if (! is_string($search)) {
            return null;
        }

        return $search;
    }

    protected function prepareForValidation(): void
    {
        $search = $this->input('search');

        if (! is_string($search)) {
            return;
        }

        $search = Str::squish($search);

        if ($search === '') {
            $search = null;
        }

        $this->merge(['search' => $search]);
    }
}
