@extends('layout.default')

@push('stack.header')
<div class="hero hero--large hero--with-cover hero--sample">
    <div class="hero__container">
        <h3 class="hero__header customFadeInUp">
            Welkom bij Gumbo Millennium
        </h3>
        <p class="hero__lead hero__lead--large customFadeInUp ">
            Dubbel L, dubbel N, dubbel genieten!
        </p>
        <p class="hero__lead">
            Wil jij ook je studententijd onvergetelijk maken?
        </p>

        <div class="actions hero__cta customFadeInUp">
            <a href="#" class="btn btn-brand btn-large hero__cta-btn">Word lid</a>
        </div>
    </div>
</div>
@endpush

@section('content')
@include('example.homepage')
@endsection
