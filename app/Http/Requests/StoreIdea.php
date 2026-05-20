<?php

namespace App\Http\Requests;

use App\Data\Ideas\IdeaData;
use App\Models\CodeRepository;
use App\Models\Idea;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreIdea extends FormRequest
{
    private const MAX_TAGS = 8;

    private const MAX_TAG_LENGTH = 32;

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
            'tagline' => 'required|string|max:60',
            'summary' => 'required|string|max:240',
            'tags' => 'nullable|string|max:240',
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
            'status' => 'in:open,closed',
        ];
    }

    public function messages(): array
    {
        return [
            'tagline.required' => 'The tagline field is required',
            'tagline.max' => 'The tagline may not be greater than 60 characters.',
            'summary.required' => 'The summary field is required',
            'summary.max' => 'The summary may not be greater than 240 characters.',
            'tags.max' => 'Tags may not be greater than 240 characters in total.',
            'content.required' => 'The description field is required',
            'content.max' => 'The description may not be greater than 20000 characters.',
            'repository_name.regex' => 'Repository names may only contain letters, numbers, dashes, and underscores.',
            'repository_name.unique' => 'You already have an idea using this repository name.',
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $tags = $this->tagValues();

                if (count($tags) > self::MAX_TAGS) {
                    $validator->errors()->add('tags', 'Use no more than 8 tags.');
                }

                foreach ($tags as $tag) {
                    if (mb_strlen($tag) > self::MAX_TAG_LENGTH) {
                        $validator->errors()->add('tags', 'Each tag must be 32 characters or fewer.');
                    }

                    if (! preg_match('/^[a-z0-9][a-z0-9 _-]*$/', $tag)) {
                        $validator->errors()->add('tags', 'Tags may only contain letters, numbers, spaces, dashes, and underscores.');
                    }
                }
            },
        ];
    }

    public function toData(): IdeaData
    {
        $this->validated();

        return new IdeaData(
            title: $this->string('title')->toString(),
            tagline: $this->string('tagline')->toString(),
            summary: $this->string('summary')->toString(),
            tags: $this->tagValues(),
            repositoryName: $this->string('repository_name')->toString(),
            communication: $this->string('communication')->toString(),
            content: $this->string('content')->toString(),
            status: $this->string('status', 'open')->toString()
        );
    }

    private function tagValues(): array
    {
        $tags = $this->input('tags');

        if (! is_string($tags)) {
            return [];
        }

        return collect(explode(',', $tags))
            ->map(fn (string $tag): string => Str::of($tag)->squish()->lower()->toString())
            ->filter()
            ->unique()
            ->values()
            ->all();
    }
}
