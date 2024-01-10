@component('mail::layout')
    {{-- Forwarded slots --}}
    @slot('summary', $summary ?? null)
    @slot('mailImage', $mailImage ?? null)
    @slot('html', $html ?? null)
    @slot('greeting', $greeting ?? null)
    @slot('subcopy', $subcopy ?? null)

    {{-- Body --}}
    <div class="{{ $markdown ?? false ? 'prose' : '' }}">
        {{ $slot }}
    </div>
@endcomponent
