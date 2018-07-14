@extends('layout.default')

@push('stack.header')
<div class="hero hero--homepage hero--full-height">
    <div class="hero__inner">
        <h2 class="hero__title customFadeInUp">
            Wij zijn Gumbo Millennium
        </h2>
        <p class="hero__subtitle customFadeInUp">
            Dubbel L, Dubbel N, Dubbel genieten!
        </p>
        <p class="hero__body">
            Maak je studententijd onvergetelijk!
        </p>

        <div class="actions customFadeInUp">
            <a href="#" class="btn-pill btn-pill-brand btn-pill-lg">Word lid</a>
        </div>
    </div>
</div>

@endpush

@section('content')
@include('example.homepage')
@endsection
