<div class="container">
    <ul class="flex flex-row items-center list-none my-4 breadcrumbs">
        <li>
            <a href="{{ route('home') }}" class="no-underline hover:underline breadcrumbs__item">
                @icon('solid/home', 'h-4')
                <div class="sr-only">Home</div>
            </a>
        </li>
        @foreach ($items as $url => $text)
            <li>
                @icon('solid/chevron-right', 'h-4')
            </li>
            @empty ($url)
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
