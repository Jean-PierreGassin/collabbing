<?php

namespace Database\Factories;

use App\Models\CodeRepository;
use App\Models\Idea;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Idea> */
class IdeaFactory extends Factory
{
    protected $model = Idea::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'title' => $this->faker->sentence(3),
            'tagline' => $this->faker->sentence(8),
            'summary' => $this->faker->sentence(14),
            'tags' => ['product', 'collaboration'],
            'communication' => 'Slack',
            'collaboration_stage' => Idea::COLLABORATION_STAGE_NEEDS_SHAPING,
            'help_wanted' => [Idea::HELP_PRODUCT, Idea::HELP_FEEDBACK],
            'help_wanted_note' => 'Useful feedback on scope and first steps would help.',
            'first_contribution' => 'Review the pitch and suggest a small first improvement.',
            'applications_open' => true,
            'applications_closed_note' => null,
            'communication_style' => Idea::COMMUNICATION_STYLE_SLACK,
            'communication_note' => 'Async planning works best.',
            'getting_started_notes' => null,
            'getting_started_notes_updated_at' => null,
            'content' => $this->faker->paragraph,
        ];
    }

    public function withCodeRepository(?string $name = null, array $attributes = []): static
    {
        return $this->afterCreating(function (Idea $idea) use ($name, $attributes): void {
            $owner = $idea->user;
            $repositoryOwner = null;

            if ($owner instanceof User) {
                $repositoryOwner = $owner->githubUsername();
            }

            $idea->codeRepository()->create(array_merge([
                'provider' => CodeRepository::PROVIDER_GITHUB,
                'status' => CodeRepository::STATUS_PLANNED,
                'owner' => $repositoryOwner,
                'name' => $name ?? $this->faker->slug(2),
            ], $attributes));
        });
    }
}
