@component('mail::layout')

{{-- Greeting --}}
@slot('header')
Greeting: {{ $greeting or "Hallo!" }}
@endslot

{{-- Intro Lines --}}
@foreach ($introLines as $line)
{{ $line }}

@endforeach

{{-- Action Button --}}
@isset($actionText)
@component('mail::button', ['url' => $actionUrl, 'color' => $color])
{{ $actionText }}
@endcomponent
@endisset

{{-- Outro Lines --}}
@foreach ($outroLines as $line)
{{ $line }}

@endforeach

{{-- Salutation --}}
@if (!empty($salutation))
{{ $salutation }}
@else
Met vriendelijke groet,
Gumbo Millennium
@endif

{{-- Subcopy --}}
@isset($actionText)
@slot('subcopy')
<p class="mail-line">Als je problemen hebt met de "{{ $actionText }}" knop, kopieer en plak de URL hieronder in je webbrowser</p>
<p class="mail-line"><a href="{{ $actionUrl }}" class="mail-link mail-link--gray">{{ $actionUrl }}</a></p>
@endslot
@endisset
@endcomponent
