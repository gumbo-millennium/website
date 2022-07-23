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

  {{-- Start of body --}}
  <main class="max-w-7xl mx-auto px-2 pb-10 lg:py-12 lg:px-8">
    <div class="lg:grid lg:grid-cols-12 lg:gap-x-5">
      <aside class="py-6 px-2 sm:px-6 lg:py-0 lg:px-0 lg:col-span-3">
        <nav class="space-y-1">
          @foreach ($accountRoutes as $name => [$label, $icon])
          <?php
              $isCurrent = $name === $activeRoute;
              $linkClass = $isCurrent
                  ? "bg-gray-50 text-gray-900"
                  : "text-gray-900 hover:text-gray-900 hover:bg-gray-50";
              $iconClass = $isCurrent
                  ? "text-brand-500"
                  : "text-gray-400 group-hover:text-gray-500";
              ?>
            <a href="{{ route($name) }}"
              class="relative group rounded-lg p-3 flex items-center text-sm font-medium {{ $linkClass }}" @if($isCurrent)
              aria-current="page" @endif>
              <x-icon class="{{ $iconClass }} flex-shrink-0 -ml-1 mr-3 h-6 w-6" :icon="$icon" />
              <span class="truncate"> {{ $label }} </span>
              @if ($isCurrent)
              <span class="absolute -left-2 top-[calc(50% - 12px)] w-1 h-7 rounded-sm bg-brand-500"></span>
              @endif
            </a>
          @endforeach
        </nav>
      </aside>

      <div class="sm:px-6 lg:px-0 lg:col-span-9">
        @if (! $hideFlash && flash()->message)
        <div class="mb-6">
          <x-alert :message="flash()->message" :level="flash()->class" />
        </div>
        @endif

        <div id="content">
          @unless ($hideTitle)
          <div class="pb-2 mb-4 border-b border-gray-200">
            <h1 class="text-2xl font-title">{{ $accountTitle }}</h1>
          </div>
          @endunless
          {{ $slot }}
        </div>
      </div>
    </div>
  </main>

  {{-- Sponsor --}}
  <x-layout.sponsor />

  {{-- Footer --}}
  <x-layout.footer />

  {{-- Logout form --}}
  <x-layout.logout-form />
</body>
