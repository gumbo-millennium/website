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
    <div class="my-4 p-4 mb-4 border border-brand-600 rounded flex flex-row items-center">
        @icon('solid/ad', 'mr-2 icon-lg text-brand-600')
        <p>Dit is een advertorial door {{ $item->sponsor }}.</p>
    </div>
    @endif

    <div class="plain-content">
        {!! $item->html !!}
    </div>
</div>
@endsection
