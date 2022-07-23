<!DOCTYPE html>
<html lang="nl">

<head>
  <x-layout.head-section />
</head>

<body @if(!empty($bodyClass)) class="{{ $bodyClass }}" @endif>
  {{-- Skip to content (a11y) --}}
  <x-layout.content-skip />

  {{-- Header --}}
  <x-layout.header />

  {{-- Email verification banner --}}
  @if (Auth::user()?->hasVerifiedEmail() === false)
  <x-layout.verify-banner />
  @endif

  {{-- Alerts --}}
  @if (! $hideFlash && flash()->message)
  <div class="container">
    <x-alert :message="flash()->message" :level="flash()->class" />
  </div>
  @endif

    {{-- Content main --}}
  <main class="main" id="content">
    {{ $slot }}
  </main>

  {{-- Sponsor --}}
  <x-layout.sponsor />

  {{-- Footer --}}
  <x-layout.footer />

  {{-- Logout form --}}
  <x-layout.logout-form />
</body>
