@component('mail::message')

{{-- Greeting --}}
@slot('header')
{{ $greeting or "Hallo!" }}
@endslot

{{-- Intro Lines --}}
@foreach ($introLines as $line)
<p class="mail-line">{{ $line }}</p>
@endforeach

{{-- Action Button --}}
@isset($actionText)
@component('mail::button', ['url' => $actionUrl, 'color' => $color])
{{ $actionText }}
@endcomponent
@endisset

{{-- Outro Lines --}}
@foreach ($outroLines as $line)
<p class="mail-line">{{ $line }}</p>
@endforeach

{{-- Salutation --}}
@slot('footer')
@if (! empty($salutation))
{{ $salutation }}
@else
Met vriendelijke groet,
Gumbo Millennium
@endif
@endslot

{{-- Subcopy --}}
@isset($actionText)
@slot('subcopy')
<p class="mail-line">Als je problemen hebt met de "{{ $actionText }}" knop, kopieer en plak de URL hieronder in je webbrowser</p>
<p class="mail-line"><a href="{{ $actionUrl }}" class="mail-link mail-link--gray">{{ $actionUrl }}</a></p>
@endslot
@endisset
@endcomponent
