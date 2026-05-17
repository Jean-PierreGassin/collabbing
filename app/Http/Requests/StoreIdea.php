<?php

namespace App\Http\Requests;

use App\Models\CodeRepository;
use App\Models\Idea;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

/**
 * Class StoreIdea
 */
class StoreIdea extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        /** @var Idea|null $idea */
        $idea = $this->route('idea');
        $repository = $idea?->codeRepository;

        return [
            'title' => 'required|max:100',
            'summary' => 'required|string|max:240',
            'repository_name' => [
                'required',
                'string',
                'max:100',
                'regex:/^[A-Za-z0-9_-]+$/',
                Rule::unique('code_repositories', 'name')
                    ->where('provider', CodeRepository::PROVIDER_GITHUB)
                    ->where('owner', Auth::user()?->githubUsername())
                    ->ignore($repository?->id),
            ],
            'communication' => 'required|max:50',
            'content' => 'required|max:20000',
            'status' => 'in:open,closed',
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     */
    public function messages(): array
    {
        return [
            'summary.required' => 'The summary field is required',
            'summary.max' => 'The summary may not be greater than 240 characters.',
            'content.required' => 'The description field is required',
            'content.max' => 'The description may not be greater than 20000 characters.',
            'repository_name.regex' => 'Repository names may only contain letters, numbers, dashes, and underscores.',
            'repository_name.unique' => 'You already have an idea using this repository name.',
        ];
    }
}
