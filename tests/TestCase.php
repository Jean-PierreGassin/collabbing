<?php

namespace Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication, RefreshDatabase, SeedsDatabase;

    protected $user;

    protected $idea;

    protected $comment;

    protected $supporter;

    protected $applicant;

    public function __construct()
    {
        parent::setUp();
        parent::__construct();

        $this->createUserAndIdeas();
    }
}
