<?php

namespace Tests\Feature;

use App\Models\Idea;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class IdeaSearchTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_search_query_is_normalized_and_preserved_on_pagination_links(): void
    {
        $owner = User::factory()->create();

        foreach (range(1, 11) as $index) {
            Idea::factory()
                ->for($owner, 'user')
                ->create([
                    'title' => "Collab search result {$index}",
                ]);
        }

        Idea::factory()
            ->for($owner, 'user')
            ->create([
                'title' => 'Unrelated idea',
            ]);

        $this
            ->get(route('ideas.index', ['search' => '  Collab   search  ']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Ideas/Index')
                ->where('keyword', 'Collab search')
                ->has('searchResults.items', 10)
                ->where('searchResults.nextPageUrl', function (?string $url): bool {
                    if (! is_string($url) || str_contains($url, '++Collab')) {
                        return false;
                    }

                    parse_str((string) parse_url($url, PHP_URL_QUERY), $query);

                    return ($query['search'] ?? null) === 'Collab search';
                }));
    }

    public function test_search_treats_sql_wildcards_as_literal_characters(): void
    {
        $owner = User::factory()->create();

        Idea::factory()
            ->for($owner, 'user')
            ->create([
                'title' => 'Ordinary idea',
            ]);

        $this
            ->get(route('ideas.index', ['search' => '%']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Ideas/Index')
                ->where('keyword', '%')
                ->has('searchResults.items', 0));
    }

    public function test_search_query_is_limited_to_a_reasonable_length(): void
    {
        $this
            ->from(route('ideas.index'))
            ->get(route('ideas.index', ['search' => str_repeat('a', 81)]))
            ->assertRedirect(route('ideas.index'))
            ->assertSessionHasErrors('search');
    }
}
