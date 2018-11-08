@extends('layout.default')

{{-- Disable transparent navbar --}}
@push('navbar-classes')
navbar--opaque
@endpush

@section('content')
<div class="d-block my-3">&nbsp;</div>
<div class="container my-4">
    <h1>
        Aanmelden bij Gumbo Millennium
    </h1>
</div>

@if ($page ?? null)
{!! $page->content !!}
@endif

{{-- Sign up form --}}
<div class="gumbo-shaded-block unique-selling-points">
    <div class="container">
        <div class="unique-selling-points__header">
            <h3 class="unique-selling-points__header-title">Aanmelden voor Gumbo Millennium?</h3>
            <p class="unique-selling-points__header-text">
                Vul onderstaand formulier in om lid te worden van Gumbo Millennium. Het bestuur neemt
                daarna contact met je op.
            </p>
        </div>
        <form novalidate method="POST" action="{{ route('join') }}" class="unique-selling-points__features">
            @csrf

            {{-- Display errors --}}
            @include('join/errors')

            {{-- Display form --}}
            @include('join/form')

            {{-- Display privacy acceptance --}}
            @include('join/privacy')

            {{-- Display newsletter --}}
            @include('join/newsletter')

            {{-- Display submit button --}}
            <div class="row">
                <div class="col-sm-10 offset-sm-2 col-md-8 offset-md-2 col-lg-6 offset-lg-3">
                    <button type="submit" class="btn btn-brand btn-block btn-lg mt-3">
                        Aanmelden
                    </button>

                    <p class="text-muted mt-1 text-center">We sturen een kopie van je aanmelding per mail.</p>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
