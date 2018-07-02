@extends('layout.auth')

@section('content')
<section class="module">
    <div class="container">
        <div class="row">
            <div class="col-md-4 m-auto">
                {{-- Show logo --}}
                @include('auth.items.logo')

                {{-- Login form --}}
                <form class="up-form" method="post">
                    @csrf
                    <div class="form-group">
                        <input class="form-control form-control-lg" type="text" name="username" placeholder="Gebruikersnaam" value="{{ old('username') }}">
                    </div>
                    <div class="form-group">
                        <input class="form-control form-control-lg" type="password" name="password" placeholder="Wachtwoord">
                    </div>
                    <div class="form-group">
                        <button class="btn btn-block btn-lg btn-round btn-brand" type="submit">Inloggen</button>
                    </div>
                </form>

                {{-- Login actions --}}
                <div class="up-help">
                    <p><a href="/auth/reset">Forgot your password?</a></p>
                    <p>Don't have an account yet? <a href="#">Sign up</a></p>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
