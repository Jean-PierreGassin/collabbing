@section('title', 'Submit Feedback')
@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="pricing-header px-3 py-3 pt-md-5 pb-md-4 mx-auto text-center">
            <h1 class="display-4">Pricing</h1>
            <p class="lead">Get the most out of Collabbing's features, choose a pricing plan below.</p>
        </div>

        <div class="card-deck mb-3 text-center">
            <div class="card mb-4 box-shadow">
                <div class="card-header">
                    <h4 class="my-0 font-weight-normal">Free</h4>
                </div>
                <div class="card-body">
                    <h1 class="card-title pricing-card-title">$0
                        <small class="text-muted">/ mo</small>
                    </h1>
                    <ul class="list-unstyled mt-3 mb-4">
                        <li>Limited idea post's per-day</li>
                        <li>Limited idea applications per-day</li>
                        <li><s>Automatic idea repository management</s></li>
                    </ul>
                    @guest
                        <a href="{{ route('register') }}" class="btn btn-lg btn-block btn-outline-primary">
                            Sign up for free
                        </a>
                    @endguest
                </div>
            </div>
            <div class="card mb-4 box-shadow">
                <div class="card-header">
                    <h4 class="my-0 font-weight-normal">Premium</h4>
                </div>
                <div class="card-body">
                    <h1 class="card-title pricing-card-title">$8 USD
                        <small class="text-muted">/ mo</small>
                    </h1>
                    <ul class="list-unstyled mt-3 mb-4">
                        <li>Unlimited idea posting</li>
                        <li>Unlimited idea applications</li>
                        <li>Automatic idea repository management</li>
                    </ul>
                    <a href="{{ route('ideas.index') }}" class="btn btn-lg btn-block btn-primary disabled">
                        <s>Get started</s>
                    </a>
                    <br>
                    <h5>Collabbing is currently free for everyone!</h5>
                </div>
            </div>
        </div>
    </div>
@endsection