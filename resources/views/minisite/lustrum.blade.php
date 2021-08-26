@extends('layout.main')

@section('content')
{{-- Birthday hedaer --}}
<div class="lustrum-header">
    <div class="lustrum-header__inner">
        <div class="container md:flex items-center py-8 md:py-16">
            <div class="w-full lg:w-8/12">
                <div class="lg:py-8">
                    <p class="text-2xl font-title font-medium text-white mb-4">Er is er een jarig</p>

                    <h1 class="text-4xl md:text-7xl font-title fond-medium text-white">
                        Gumbo Millennium viert <br />
                        <span class="font-bold">het 6e lustrum</span>
                    </h1>
                </div>
            </div>

            <div class="hidden w-4/12 text-right lg:block">
                <img src="{{ mix('images/logo-glass-white.svg') }}" alt="Gumbo Millennium"
                    class="h-80" />
            </div>
        </div>

        <div class="p-2 text-sm text-white">
            Photo by <a rel="noopener nofollow" class="text-white" target="_blank" href="https://unsplash.com/@melpoole">Mel Poole</a>
            on <a rel="noopener nofollow" class="text-white" target="_blank" href="https://unsplash.com/photos/usk6ENzJfX0">Unsplash</a>
        </div>
    </div>
</div>

{{-- Year counter --}}
<div class="bg-brand-600 py-8">
    <div class="container">
        <div class="text-3xl lg:text-5xl font-medium text-white font-title text-center">
            1991 - <b class="font-bold">2021</b>
        </div>
    </div>
</div>

{{-- Information page --}}
@if ($page && !empty($page->html))
<div class="py-8 bg-gray-100">
    <div class="container">
        {{-- Title --}}
        <p class="text-center text-gray-primary-1 mb-4">{{ $page->summary }}</p>
        <h2 class="text-3xl text-medium font-title mb-8 text-center">{{ $page->title }}</h2>

        {{-- Page body --}}
        <div class="plain-content">
            {!! $page->html !!}
        </div>
    </div>
</div>
@endif

{{-- Activities --}}
@if ($activities->isNotEmpty())
<div class="py-8">
    <div class="container">
        <div class="container pt-8">
            {{-- Title --}}
            <p class="text-center text-gray-primary-1 mb-4">Bomvol met activiteiten</p>
            <h2 class="text-3xl text-medium font-title mb-8 text-center">Activiteiten</h2>

            {{-- Activity cards --}}
            <div class="card-grid">
                @foreach ($activities as $activity)
                <div class="card-grid__item">
                    @include('activities.bits.single')
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endif
@endsection
