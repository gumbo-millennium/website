@component('mail::layout')
    {{-- Mail lead --}}
    @if(isset($header) && !empty($header))
        @slot('header')
            {{ $header }}
        @endslot
    @endif

    {{-- Body --}}
    {{ $slot }}

    {{-- Subcopy --}}
    @if(isset($subcopy) && !empty($subcopy))
        @slot('subcopy')
            {{ $subcopy }}
        @endslot
    @endif

    {{-- Image --}}
    @if (!empty($mailImage))
        @slot('mailImage', $mailImage)
    @endif
@endcomponent
