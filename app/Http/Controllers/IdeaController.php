<?php

namespace App\Http\Controllers;

use App\Idea;
use App\Http\Requests\StoreIdea;
use App\User;
use Illuminate\Support\Facades\Auth;

class IdeaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $trendingIdeas = Idea::where('status', 'open')
            ->withCount('supporters')
            ->orderBy('supporters_count', 'desc')
            ->orderBy('created_at', 'desc')
            ->limit(3)
            ->paginate();

        $trendingIds = $trendingIdeas->pluck('id');

        $ideas = Idea::where('status', 'open')
            ->orderBy('created_at', 'desc')
            ->whereNotIn('id', $trendingIds->all())
            ->paginate(10);

        return view('idea.list', compact('trendingIdeas', 'ideas'));
    }

    /**
     * Display the dashboard for an idea
     *
     * @param  \App\Idea $idea
     * @return \Illuminate\Http\Response
     */
    public function dashboard(Idea $idea)
    {
        $applications = $idea->pendingApplications()->get();
        $collaborators = $idea->approvedApplications()->get();

        return view('idea.manage', compact(
                'idea',
                'applications',
                'collaborators'
            )
        );
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('idea.edit-add');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreIdea $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreIdea $request)
    {
        $user = $request->user();
        $idea = $user->ideas()->create($request->validated());

        return redirect()
            ->route('ideas.show', compact('idea'))
            ->with('status', 'Idea successfully created');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Idea $idea
     * @return \Illuminate\Http\Response
     */
    public function show(Idea $idea)
    {
        if (Auth::user()) {
            $collaborator = $idea->hasApplicationFromUser(Auth::user()->id, 'approved');
            $applicant = $idea->hasApplicationFromUser(Auth::user()->id, 'pending');
            $supporter = $idea->hasSupportFromUser(Auth::user()->id);
        }

        return view('idea.single', compact(
                'idea',
                'collaborator',
                'applicant',
                'supporter'
            )
        );
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Idea $idea
     * @return \Illuminate\Http\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function edit(Idea $idea)
    {
        $this->authorize('manage', $idea);

        return view('idea.edit-add', compact('idea'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\StoreIdea $request
     * @param  \App\Idea $idea
     * @return \Illuminate\Http\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function update(StoreIdea $request, Idea $idea)
    {
        $this->authorize('update', $idea);

        $idea->update($request->validated());
        $idea->save();

        return redirect()
            ->route('ideas.show', compact('idea'))
            ->with('status', 'Idea successfully edited');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Idea $idea
     * @return \Illuminate\Http\Response
     */
    public function destroy(Idea $idea)
    {
        //
    }

    public function createRepository(Idea $idea)
    {
        $this->authorize('createRepository', $idea);

        if ($idea->repository) {
            return route('ideas.dashboard', $idea);
        }

        try {
            $gitHub = Auth::user()->createGithubClient();

            $gitHub->repo()->create($idea->repository_name);
            $idea->repository = 1;
            $idea->save();
        } catch (\Exception $e) {
            return redirect()
                ->route('ideas.dashboard', $idea)
                ->with('status', 'Something went wrong, try again 🙉');
        }

        return redirect()
            ->route('ideas.dashboard', $idea)
            ->with('status', 'Repository created 🔥');
    }

    public function inviteUsersToRepository(Idea $idea)
    {
        $this->authorize('inviteUsersToRepository', $idea);

        try {
            $gitHub = Auth::user()->createGithubClient();
            $user = User::find(Auth::user()->id);

            foreach ($idea->approvedApplications()->get() as $collaborator) {
                $gitHub->repo()
                    ->collaborators()
                    ->add(
                        $user->github_username,
                        $idea->repository_name,
                        $collaborator->user->github_username
                    );
            }
        } catch (\Exception $e) {
            dd($e->getMessage());
            return redirect()
                ->route('ideas.dashboard', $idea)
                ->with('status', 'Something went wrong, try again 🙉');
        }

        return redirect()
            ->route('ideas.dashboard', $idea)
            ->with('status', 'All Collaborators has been invited 🤟');
    }
}
