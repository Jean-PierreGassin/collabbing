<?php

namespace Tests\Unit\Users;

use Tests\TestCase;

class IdeaTest extends TestCase
{
    public function testThatAUserHasAnIdea()
    {
        $this->assertInstanceOf(App\Idea::class, $this->idea);
    }

    public function testThatAUserHasManyIdeas()
    {
        $this->assertGreaterThan(1, $this->user->ideas->count());
    }

    public function testThatAnIdeaHasOneUser()
    {
        $this->assertCount(1, $this->idea->user);
        $this->assertInstanceOf(App\User::class, $this->idea->user);
    }
}