@extends('layout.main')

@php
$authorName = optional($item->author)->display_name ?? 'Onbekend';
$isSponsor = !empty($item->sponsor);
if ($isSponsor) {
    $authorName = $item->sponsor;
}

$postTimestamp = $item->published_at ?? $item->created_at;
$postIso = $postTimestamp->toIso8601String();
$postDate = $postTimestamp->isoFormat('D MMMM, Y');
@endphp

@section('content')
<article class="my-16">
{{-- Header --}}
<header class="container container--md">
    {{-- Title --}}
    <h1 class="text-2xl font-title font-bold mb-6">{{ $item->title }}</h1>

    {{-- Author --}}
    <div class="py-4 mb-5 border-gray-200 border-t border-b">
        <div class="flex flex-row row items-center">
            <div class="col mb-8 md:mb-0 md:w-7/12">
                <p class="mb-0 font-bold">
                    {{ $authorName }}
                    @if ($isSponsor)
                    @icon('solid/ad', 'ml-2')
                    @endif
                </p>
                <time timestamp="{{ $postIso }}" class="mb-0 text-gray-600">{{ $postDate }}</time>
            </div>
            <div class="col md:w-5/12 flex flex-wrap flex-row items-center md:justify-end">
                <p class="text-sm uppercase font-medium text-gray-600 w-full md:w-auto md:mr-2 mb-0 leading-none">Delen:</p>
                <a href="#share-facebook" class="p-2 mr-2 text-gray-600" aria-label="Delen op Facebook">
                    @icon('brands/facebook-f', 'h-4')
                </a>
                <a href="#share-twitter" class="p-2 mr-2 text-gray-600" aria-label="Delen op Twitter">
                    @icon('brands/twitter', 'h-4')
                </a>
                <a href="#share-instagram" class="p-2 mr-2 text-gray-600" aria-label="Delen op Instagram">
                    @icon('brands/instagram', 'h-4')
                </a>
                <a href="#share-telegram" class="p-2 text-gray-600" aria-label="Delen op Telegram">
                    @icon('brands/telegram', 'h-4')
                </a>
            </div>
        </div>
    </div>
</header>

{{-- Content --}}
<div class="container container--md leading-loose">
    <div class="plain-content plain-content--narrow">
        {!! $item->html !!}
    </div>
</div>

{{-- End --}}
</article>
@endsection
