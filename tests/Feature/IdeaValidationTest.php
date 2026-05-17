<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class IdeaValidationTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_repository_name_rejects_spaces(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->post(route('ideas.store'), [
                'title' => 'A useful collaboration tool',
                'repository_name' => 'repo with spaces',
                'communication' => 'Slack',
                'content' => 'A focused pitch for a useful collaboration tool.',
                'status' => 'open',
            ]);

        $response->assertSessionHasErrors('repository_name');
    }
}
