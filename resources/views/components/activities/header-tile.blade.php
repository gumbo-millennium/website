<div class="border-b border-gray-200 px-8 py-4 flex items-center">
    <x-icon :icon="$icon" class="h-8 mr-4 text-gray-400" />

    <div class="flex flex-col justify-start">
        @if (isset($title))
        <strong>{{ $title }}</strong>
        <div class="hidden lg:block">
            {{ $slot }}
        </div>
        @else
        {{ $slot }}
        @endif
    </div>
</div>
