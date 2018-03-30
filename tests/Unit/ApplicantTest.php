<?php

namespace Tests\Unit\Ideas;

use Tests\TestCase;

class ApplicantTest extends TestCase
{
    public function testThatAnIdeaHasManyApplicants()
    {
        $this->assertInstanceOf(App\Idea::class, $this->applicant->idea);
    }

    public function testThatAnApplicantHasOneIdea()
    {
        $this->assertGreaterThan(1, $this->idea->applicants->count());
    }

    public function testThatAnApplicantHasOneOwner()
    {
        $this->assertCount(1, $this->applicant->user);
        $this->assertInstanceOf(App\User::class, $this->applicant->user);
    }
}