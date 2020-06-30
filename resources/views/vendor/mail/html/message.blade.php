@component('mail::layout')
    {{-- Forwarded slots --}}
    @slot('summary', $summary ?? null)
    @slot('mailImage', $mailImage ?? null)
    @slot('html', $html ?? null)
    @slot('greeting', $greeting ?? null)
    @slot('subcopy', $subcopy ?? null)

    {{-- Body --}}
    {{ $slot }}
@endcomponent
