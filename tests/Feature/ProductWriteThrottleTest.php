<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ProductWriteThrottleTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_authenticated_product_writes_are_rate_limited_per_user(): void
    {
        $user = User::factory()->create();

        for ($attempt = 1; $attempt <= 21; $attempt++) {
            $response = $this
                ->actingAs($user)
                ->post(route('ideas.store'), [
                    'title' => "Rate limited idea {$attempt}",
                    'repository_name' => "rate-limited-idea-{$attempt}",
                    'communication' => 'Slack',
                    'content' => 'A bounded write path keeps automated posting from overwhelming the workspace.',
                    'status' => 'open',
                ]);
        }

        $response->assertTooManyRequests();
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Error')
            ->where('status', 429));
    }

    public function test_throttle_does_not_block_reading_idea_pages(): void
    {
        $user = User::factory()->create();

        for ($attempt = 1; $attempt <= 21; $attempt++) {
            $this
                ->actingAs($user)
                ->post(route('ideas.store'), [
                    'title' => "Read allowed idea {$attempt}",
                    'repository_name' => "read-allowed-idea-{$attempt}",
                    'communication' => 'Slack',
                    'content' => 'The write limiter should not prevent normal browsing.',
                    'status' => 'open',
                ]);
        }

        $this->get(route('ideas.index'))->assertOk();
    }
}
