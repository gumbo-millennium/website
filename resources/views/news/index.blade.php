@extends('layout.main')

@section('content')
<header class="header header--activity">
    <div class="container header__container">
        <h1 class="header__title">Nieuws</h1>
        <p class="header__subtitle">Het laatste nieuws van Gumbo Millennium</p>
    </div>
</header>

<div class="container after-header">
    <div class="mb-8">
        <p>De laatste propoganda vanuit Gumbo Millennium.</p>
    </div>

    @foreach ($items as $item)
    @php
    $postDate = $item->published_at ?? $item->created_at;
    $postMonth = trim($postDate->isoFormat('MMM'), '.');
    $postDay = $postDate->isoFormat('DD');
    @endphp
    <article class="flex flex-row mb-8 py-4">
        <div class="flex flex-col items-center mr-4 px-4">
            <span class="text-block text-sm uppercase text-black">{{ $postMonth }}</span>
            <span class="text-block text-xl text-brand-600">{{ $postDay }}</span>
            <span class="text-block text-lg text-center">
                @if ($item->sponsor)@icon('solid/ad')@endif
            </span>
        </div>
        <div class="flex-grow">
            <h2 class="font-bold text-brand-600 text-2xl">
                <a href="{{ route('news.show', ['news' => $item]) }}">
                    {{ $item->title }}
                </a>
            </h2>
            <div class="flex flex-row flex-wrap text-sm">
                @include('news.parts.item-summary', ['item' => $item])
            </div>
        </div>
    </article>
    @endforeach
</div>
@endsection
