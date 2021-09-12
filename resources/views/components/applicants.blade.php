<div id="accordion">
    <div class="card mb-2">
        <div class="card-header" id="{{ $application->user_id }}-header">
            <h6 class="mb-0">
                <a href="{{ route('users.show', $application->user->username) }}">{{ $application->user->name }}'s Application</a>
            </h6>
        </div>

        <div id="{{ $application->id }}">
            <div class="card-body">
                {!! nl2br(e($application->content)) !!}
            </div>

            <h6 class="text-muted text-right mr-2">Submitted {{ $idea->created_at->diffForHumans() }}</h6>

            <div class="card-footer">
                <div class="row">
                    <div class="col-sm">
                        @can('deleteApplication', $idea)
                            {!! Form::open([
                                'route' => ['ideas.applications.destroy', $idea, $application],
                                'method' => 'DELETE'
                            ]) !!}
                            {!! Form::submit('Decline this Application 👎', ['class' => 'btn btn-danger btn-sm']) !!}
                            {!! Form::close() !!}
                        @endcan
                    </div>

                    <div class="col-sm text-sm-right">
                        @can('updateApplication', $idea)
                            {!! Form::open([
                                'route' => ['ideas.applications.approve', $idea, $application],
                                'method' => 'PUT'
                            ]) !!}
                            {!! Form::submit('Approve this Application ✅', ['class' => 'btn btn-success btn-sm float-right']) !!}
                            {!! Form::close() !!}
                        @endcan
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>