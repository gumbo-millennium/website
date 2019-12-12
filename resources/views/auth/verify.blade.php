@extends('layout.main')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <form class="card" method="POST" action="{{ route('verification.resend') }}">
                @csrf
                <div class="card-header">{{ __('Verify Your Email Address') }}</div>

                <div class="card-body">
                    @if (session('resent'))
                        <div class="alert alert-success" role="alert">
                            {{ __('A fresh verification link has been sent to your email address.') }}
                        </div>
                    @endif

                    <p>
                        {{ __('Before proceeding, please check your email for a verification link.') }}
                    </p>
                    <p>
                        {{ __('If you did not receive the email') }},<br />
                        <button class="btn btn-brand" type="submit">{{ __('click here to request another') }}.</button>
                    </p>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
