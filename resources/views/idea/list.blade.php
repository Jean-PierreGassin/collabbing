@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center mb-3">
            <div class="col-md-8">
                @if (session('status'))
                    <div class="alert alert-success">
                        {{ session('status') }}
                    </div>
                @endif

                @if (session('errors'))
                    <div class="alert alert-danger">
                        @foreach (session('errors')->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </div>
                @endif

                @if (isset($searchResults))
                    <div id="search-results" class="mb-4">
                        <h3>Search Results</h3>
                        @if (count($searchResults) > 0)
                            @include('components.ideas', [
                                'container' => 'ideas',
                                'ideas' => $searchResults,
                            ])
                        @else
                            Darn it, we couldn't find anything related to "{{ Request::get('search') }}"
                        @endif
                    </div>
                @endif

                <div id="trending-ideas" class="mb-4">
                    <h3>Trending Ideas</h3>
                    @if (isset($trendingIdeas) && count($trendingIdeas) > 0)
                        @include('components.ideas', [
                            'container' => 'trending-ideas',
                            'ideas' => $trendingIdeas,
                        ])
                    @else
                        That's unusual, nothing seems to be trending 😔
                    @endif
                </div>

                <div id="ideas">
                    <h3>Recent Ideas</h3>
                    @if (isset($ideas) && count($ideas) > 0)
                        @include('components.ideas', [
                            'container' => 'ideas',
                            'ideas' => $ideas,
                        ])
                    @else
                        There doesn't seem to be anything here yet...
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection