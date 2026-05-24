<?php

namespace App\Http\Requests;

use App\Data\Ideas\IdeaData;
use App\Models\CodeRepository;
use App\Models\Idea;
use App\Models\User;
use Carbon\Carbon;
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
                'nullable',
                'string',
                'max:100',
                'regex:/^[A-Za-z0-9_-]+$/',
                Rule::unique('code_repositories', 'name')
                    ->where('provider', CodeRepository::PROVIDER_GITHUB)
                    ->where('owner', $repositoryOwner)
                    ->ignore($repository?->id),
            ],
            'communication' => 'required|max:50',
            'collaboration_stage' => [
                'nullable',
                'string',
                Rule::in(Idea::collaborationStages()),
            ],
            'help_wanted' => 'nullable|array|max:11',
            'help_wanted.*' => [
                'string',
                Rule::in(Idea::helpAreas()),
            ],
            'help_wanted_note' => 'nullable|string|max:240',
            'first_contribution' => 'nullable|string|max:1200',
            'applications_open' => 'nullable|boolean',
            'applications_closed_note' => 'nullable|string|max:240',
            'communication_style' => [
                'nullable',
                'string',
                Rule::in(Idea::communicationStyles()),
            ],
            'communication_note' => 'nullable|string|max:240',
            'getting_started_notes' => 'nullable|string|max:10000',
            'notify_collaborators' => 'nullable|boolean',
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
            'help_wanted.*.in' => 'Choose a valid help area.',
            'collaboration_stage.in' => 'Choose a valid collaboration stage.',
            'communication_style.in' => 'Choose a valid communication style.',
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
            repositoryName: $this->optionalString('repository_name'),
            communication: $this->string('communication')->toString(),
            content: $this->string('content')->toString(),
            status: $this->string('status', 'open')->toString(),
            collaborationStage: $this->optionalString('collaboration_stage'),
            helpWanted: $this->helpWantedValues(),
            helpWantedNote: $this->optionalString('help_wanted_note'),
            firstContribution: $this->optionalString('first_contribution'),
            applicationsOpen: $this->applicationsOpenValue(),
            applicationsClosedNote: $this->optionalString('applications_closed_note'),
            communicationStyle: $this->optionalString('communication_style'),
            communicationNote: $this->optionalString('communication_note'),
            gettingStartedNotes: $this->gettingStartedNotesValue(),
            gettingStartedNotesUpdatedAt: $this->gettingStartedNotesUpdatedAt(),
            notifyCollaboratorsOfGettingStartedNotes: $this->boolean('notify_collaborators')
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

    private function optionalString(string $key): ?string
    {
        if (! $this->has($key)) {
            return $this->existingIdea()?->{$key};
        }

        $value = $this->input($key);

        if (! is_string($value)) {
            return null;
        }

        $value = Str::of($value)->trim()->toString();

        if ($value === '') {
            return null;
        }

        return $value;
    }

    private function helpWantedValues(): array
    {
        if (! $this->has('help_wanted')) {
            $helpWanted = $this->existingIdea()?->getAttributeValue('help_wanted');

            return is_array($helpWanted) ? $helpWanted : [];
        }

        $helpWanted = $this->input('help_wanted');

        if (! is_array($helpWanted)) {
            return [];
        }

        return collect($helpWanted)
            ->filter(fn (mixed $area): bool => is_string($area) && trim($area) !== '')
            ->map(fn (string $area): string => Str::of($area)->squish()->lower()->toString())
            ->unique()
            ->values()
            ->all();
    }

    private function applicationsOpenValue(): bool
    {
        if (! $this->has('applications_open')) {
            $applicationsOpen = $this->existingIdea()?->applications_open;

            if (is_bool($applicationsOpen)) {
                return $applicationsOpen;
            }

            return true;
        }

        return $this->boolean('applications_open');
    }

    private function gettingStartedNotesValue(): ?string
    {
        return $this->optionalString('getting_started_notes');
    }

    private function gettingStartedNotesUpdatedAt(): ?Carbon
    {
        $idea = $this->existingIdea();
        $notes = $this->gettingStartedNotesValue();

        if (! $this->has('getting_started_notes')) {
            return $this->existingGettingStartedNotesUpdatedAt($idea);
        }

        if ($notes === null) {
            return null;
        }

        if ($idea && $idea->getting_started_notes === $notes) {
            return $this->existingGettingStartedNotesUpdatedAt($idea);
        }

        return Carbon::now('UTC');
    }

    private function existingGettingStartedNotesUpdatedAt(?Idea $idea): ?Carbon
    {
        $updatedAt = $idea?->getAttributeValue('getting_started_notes_updated_at');

        if (! $updatedAt instanceof Carbon) {
            return null;
        }

        return $updatedAt;
    }

    private function existingIdea(): ?Idea
    {
        $routeIdea = $this->route('idea');

        if (! $routeIdea instanceof Idea) {
            return null;
        }

        return $routeIdea;
    }
}
