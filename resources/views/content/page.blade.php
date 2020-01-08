@extends('layout.main')

@section('title', $page->title)

@section('content')
<article>
{{-- Welcome --}}
<header class="header">
    <div class="container header__container">
        <h1 class="header__title">{{ $page->title }}</h1>
        @if (!empty($page->tagline))
        <p class="header__subtitle">
            {{ $page->tagline }}
        </p>
        @endif
    </div>
</header>

{{-- Contents --}}
<div class="container leading-relaxed content">
    {!! $page->html !!}
</div>
@endsection
