<?php

namespace App\Http\Requests;

use App\Data\Ideas\IdeaData;
use App\Models\CodeRepository;
use App\Models\Idea;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
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
        $idea = null;

        if ($routeIdea instanceof Idea) {
            $idea = $routeIdea;
        }

        $repository = $idea?->latestCodeRepository();
        $user = $this->user();
        $repositoryOwner = null;

        if ($user instanceof User) {
            $repositoryOwner = $user->githubUsername();
        }

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
                    ->where('owner', $repositoryOwner)
                    ->ignore($repository?->id),
            ],
            'communication' => 'required|max:50',
            'content' => 'required|max:20000',
            'status' => ['nullable', 'string', Rule::in(Idea::STATUSES)],
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

    public function toData(): IdeaData
    {
        $this->validated();

        $status = $this->string('status')->toString();

        if ($status === '') {
            $status = Idea::STATUS_OPEN;
        }

        return new IdeaData(
            title: $this->string('title')->toString(),
            summary: $this->string('summary')->toString(),
            repositoryName: $this->string('repository_name')->toString(),
            communication: $this->string('communication')->toString(),
            content: $this->string('content')->toString(),
            status: $status
        );
    }
}
