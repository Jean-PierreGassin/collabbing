<?php

namespace App\Http\Controllers;

use App\Models\Idea;
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
        $archive = $this->activity->archive($idea);

        return Inertia::render('Ideas/RepositoryActivity', [
            'idea' => $this->pageProps->idea($idea),
            'archive' => $archive,
        ]);
    }
}
