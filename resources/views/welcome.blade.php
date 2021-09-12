@section('title', 'Collaborate better.')
@extends('layouts.errors')

@section('content')
    <header class="text-white text-center mb-5">
        <div class="container">
            <div class="row">
                <div class="col-xl-9 mx-auto">
                    <h1 class="mb-5"><b>Collaborate</b> better.</h1>
                </div>
                <div class="col-md-10 col-lg-8 col-xl-7 mx-auto">
                    <a class="btn btn-outline-success" href="{{ route('ideas.index') }}">Browse Ideas</a>
                </div>
            </div>
        </div>
    </header>

    <section class="features-icons bg-light text-center" style="width: 100vw;">
        <div class="container">
            <div class="row">
                <div class="col-lg-4">
                    <div class="features-icons-item mx-auto mt-5 mb-5">
                        <div class="features-icons-icon d-flex mb-3">
                            <i class="fas fa-comments m-auto text-primary" style="font-size:3em;"></i>
                        </div>
                        <h3>Pitch an idea</h3>
                        <p class="lead mb-0">Market yourself and your idea to the community and get real-time
                            feedback</p>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="features-icons-item mx-auto mt-5 mb-5">
                        <div class="features-icons-icon d-flex mb-3">
                            <i class="fas fa-users m-auto text-primary" style="font-size:3em;"></i>
                        </div>
                        <h3>Be part of a team</h3>
                        <p class="lead mb-0">Find collaborators to work on your idea with you, or apply to other ideas
                            that you're interested in</p>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="features-icons-item mx-auto mt-5 mb-5">
                        <div class="features-icons-icon d-flex mb-3">
                            <i class="fas fa-flask m-auto text-primary" style="font-size:3em;"></i>
                        </div>
                        <h3>Automatic project flows</h3>
                        <p class="lead mb-0">Once your project is ready, don't worry about creating a repository - we've
                            got that sorted with our automatic integrations</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
@stop