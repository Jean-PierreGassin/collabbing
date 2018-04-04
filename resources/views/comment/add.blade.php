<div class="card">
    <div class="card-header">Share your Comment</div>

    <div class="card-body">
        {!! Form::open(['route' => ['ideas.comments.store', $idea], 'method' => 'POST']) !!}

        <div class="form-group">
            {!! Form::label('content', 'Content') !!}
            {!! Form::textarea('content', null, [
            'class' => 'form-control',
            'placeholder' => 'I liked the thing you said about the other thing, however I prefer to do it this way instead' ,
            'aria-describedby' => 'contentHelp',
            ]) !!}
            <small id="contentHelp" class="form-text text-muted">Nobody likes a bossy boots, think
                before you type.
            </small>
        </div>
    </div>

    <div class="card-footer">
        {!! Form::submit('Share Comment', ['class' => 'btn btn-success btn-sm float-right']) !!}
        {!! Form::close() !!}
    </div>
</div>