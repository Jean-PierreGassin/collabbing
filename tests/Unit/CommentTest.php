<?php

namespace Tests\Unit\Ideas;

use Tests\TestCase;

class CommentTest extends TestCase
{
    public function testThatACommentHasOneIdea()
    {
        $this->assertInstanceOf(App\Idea::class, $this->comment->idea);
    }

    public function testThatAnIdeaHasManyComments()
    {
        $this->assertGreaterThan(1, $this->idea->comments->count());
    }

    public function testThatACommentHasOneUser()
    {
        $this->assertCount(1, $this->comment->user);
        $this->assertInstanceOf(App\User::class, $this->comment->user);
    }
}