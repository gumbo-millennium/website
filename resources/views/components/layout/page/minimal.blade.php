<!DOCTYPE html>
<html lang="nl">

<head>
  <x-layout.head-section :seo="false" />
</head>

<body>
  {{-- Skip to content (a11y) --}}
  <x-layout.content-skip />

  {{-- Header --}}
  <x-layout.header :simple="true" />

  {{-- Content main --}}
  <main class="main" id="content">
    {{ $slot }}
  </main>

  {{-- Footer --}}
  <x-layout.footer />
</body>
