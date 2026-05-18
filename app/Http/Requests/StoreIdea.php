<?php

namespace App\Http\Requests;

use App\Models\CodeRepository;
use App\Models\Idea;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class StoreIdea extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $routeIdea = $this->route('idea');
        $idea = $routeIdea instanceof Idea ? $routeIdea : null;
        $repository = $idea?->latestCodeRepository();
        $user = Auth::user();

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
                    ->where('owner', $user instanceof User ? $user->githubUsername() : null)
                    ->ignore($repository?->id),
            ],
            'communication' => 'required|max:50',
            'content' => 'required|max:20000',
            'status' => 'in:open,closed',
        ];
    }

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
