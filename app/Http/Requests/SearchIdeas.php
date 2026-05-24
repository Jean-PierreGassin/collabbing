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
            'tag' => ['nullable', 'string', 'max:32', 'regex:/^[\pL\pN _-]+$/u'],
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

    public function tag(): ?string
    {
        $validated = $this->validated();
        $tag = $validated['tag'] ?? null;

        if (! is_string($tag)) {
            return null;
        }

        return $tag;
    }

    protected function prepareForValidation(): void
    {
        $search = $this->input('search');
        $tag = $this->input('tag');
        $normalized = [];

        if (is_string($search)) {
            $search = Str::squish($search);

            if ($search === '') {
                $search = null;
            }

            $normalized['search'] = $search;
        }

        if (is_string($tag)) {
            $tag = Str::of($tag)->squish()->lower()->toString();

            if ($tag === '') {
                $tag = null;
            }

            $normalized['tag'] = $tag;
        }

        if ($normalized !== []) {
            $this->merge($normalized);
        }
    }
}
