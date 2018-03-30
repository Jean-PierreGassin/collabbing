<?php

namespace Tests\Unit\Ideas;

use Tests\TestCase;

class SupporterTest extends TestCase
{
    public function testThatASupporterHasOneIdea()
    {
        $this->assertInstanceOf(App\Idea::class, $this->supporter->idea);
    }

    public function testThatAnIdeaHasManySupporters()
    {
        $this->assertGreaterThan(1, $this->idea->supporters->count());
    }

    public function testThatASupporterHasOneUser()
    {
        $this->assertCount(1, $this->supporter->user);
        $this->assertInstanceOf(App\User::class, $this->supporter->user);
    }
}