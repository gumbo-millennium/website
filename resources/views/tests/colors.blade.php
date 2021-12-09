@extends('layout.main')

@section('title', "Color view - Styling test")

@php
$colors = [
    'gray' => [50, ...range(100, 900, 100)],
    'brand' => [50, ...range(100, 900, 100)],
    'yellow' => [50, ...range(100, 900, 100)],
    'red' => [50, ...range(100, 900, 100)],
    'orange' => [50, ...range(100, 900, 100)],
    'green' => [50, ...range(100, 900, 100)],
    'blue' => [50, ...range(100, 900, 100)],
]
@endphp

@section('content')
<article class="container py-8">
    {{-- Header --}}
    <header class="page-hero">
        <h1 class="page-hero__title">Color check</h1>
        <p class="page-hero__lead">Test the color scheme</p>
    </header>

    {{-- Colors --}}
    @foreach ($colors as $name => $colorCodes)
        <div class="w-1/2 px-2 md:w-full relative mt-4">
            <h3 class="markdown no-toc mb-4 mt-8" id="gray">{{ ucfirst($name) }}</h3>
            <div class="md:-mx-2 md:-mt-5 md:flex md:flex-wrap">
                @foreach ($colorCodes as $code)
                    <div class="md:w-1/3 md:px-2">
                        <div class="flex items-center mt-5">
                            <div class="color-swatch rounded-lg shadow-inner">
                                <div class="h-12 w-12 rounded-lg shadow-inner bg-{{ $name }}-{{ $code }} text-{{ $name }}-{{ $code }} flex items-center justify-center">
                                    <x-icon icon="solid/fill" class="icon icon-lg" />
                                </div>
                            </div>
                            <div class="ml-2 text-gray-primary-3 text-xs leading-none pl-1">
                                <div class="font-semibold">{{{ $code }}}</div>
                                <div class="mt-1 font-normal opacity-75">{{ ucfirst($name) }}</div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endforeach
</article>
@endsection
