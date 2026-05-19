<?php

namespace App\Http\Controllers;

use App\Models\Idea;
use App\Models\RepositoryEvent;
use App\Services\Ideas\RepositoryActivityService;
use App\Services\Inertia\PagePropsService;
use Inertia\Inertia;
use Inertia\Response;

class IdeaRepositoryEventController extends Controller
{
    public function __construct(
        private RepositoryActivityService $activity,
        private PagePropsService $pageProps
    ) {}

    public function index(Idea $idea): Response
    {
        return Inertia::render('Ideas/RepositoryActivity', [
            'idea' => $this->pageProps->idea($idea),
            'events' => $this->pageProps->paginator(
                $this->activity->getForIdea($idea),
                fn (RepositoryEvent $event) => $this->pageProps->repositoryEvent($event)
            ),
        ]);
    }
}
