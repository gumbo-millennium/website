@extends('layout.main')

@section('content')
<header class="header header--activity">
    <div class="container header__container">
        <h1 class="header__title">{{ $item->title }}</h1>
        <div class="header__subtitle flex flex-row flex-wrap justify-center text-sm">
            @include('news.parts.item-summary', ['item' => $item])
        </div>
    </div>
</header>

<div class="container after-header">
    @if ($item->sponsor)
    <div class="notice notice--brand">
        @icon('solid/ad', 'notice__icon')
        <p>Dit is een advertorial door {{ $item->sponsor }}.</p>
    </div>
    @endif

    <div class="plain-content">
        {!! $item->html !!}
    </div>
</div>
@endsection
