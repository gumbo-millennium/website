<div class="container">
    <ul class="flex flex-row items-center list-none my-4 breadcrumbs">
        <li>
            <a href="{{ route('home') }}" class="no-underline hover:underline breadcrumbs__item">
                <x-icon icon="solid/house" class="h-4" />
                <div class="sr-only">Home</div>
            </a>
        </li>
        @foreach ($items as $url => $text)
            <li>
                <x-icon icon="solid/chevron-right" class="h-4" />
            </li>
            @if (is_int($url))
            <li>
                <span class="breadcrumbs__item cursor-default">
                    {{ $text }}
                </span>
            </li>
            @else
            <li>
                <a href="{{ $url }}" class="no-underline hover:underline breadcrumbs__item">
                    {{ $text }}
                </a>
            </li>
            @endif
        @endforeach
</div>
