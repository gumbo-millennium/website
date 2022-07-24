@props([
    'quotes',
    'delete' => false,
])
@php($lastDate = null)
@forelse ($quotes as $quote)
    <?php
    $dateIso = $quote->created_at->toIso8601String();
    $currentDate = $quote->created_at->isoFormat('dddd D MMM, Y')
    ?>

    @if ($currentDate !== $lastDate)
    <p class="my-4 text-gray-300 text-center text-sm">
        {{ $currentDate }}
    </p>
    @endif

    <div class="flex flex-row items-end justify-items-end ml-auto">
        {{-- Push right --}}
        <div class="flex-grow"></div>

        {{-- Start of chat bubble --}}
        <blockquote class="mb-4 text-right">
            {{-- Message --}}
            <p class="rounded-lg bg-blue-200 p-2">
                {{ $quote->formatted_quote }}
            </p>

            {{-- Footer --}}
            <footer class="text-right m-2 text-gray-700 text-sm">
                {{-- Author --}}
                <cite>{{ $quote->author }}</cite>

                {{-- Date --}}
                <time datetime="{{ $quote->created_at->toIso8601String() }}">
                    {{ $quote->created_at->isoFormat('HH:mm (z)') }}
                </time>
            </footer>
        </blockquote>

        @if ($delete)
        <div class="mb-6 flex-none mr-2">
            <button
                type="submit"
                form="quote-delete"
                class="appearance-none text-danger p-2"
                name="quote-id"
                value="{{ $quote->id }}"
                aria-label="Verwijder je wist-je-datje"
                data-delete-quote-id="{{ $quote->id }}"
            >
                <x-icon icon="solid/trash-alt" class="icon" />
            </button>
        </div>
        @endif
    </div>

    @php($lastDate = $currentDate)
@empty
    {{ $slot }}
@endforelse
