<x-auth-page title="Uitgelogd">
  <x-sections.transparent-header title="Okay doei"
    subtitle="Je bent succesvol uitgelogd" />

  <div class="text-center">
      @if ($next ?? null)
      <div class="grid grid-cols-2 gap-4">
          <a href="/" class="btn">@lang('Homepage')</a>
          <a href="{{ $next }}" class="btn btn--brand">@lang('Continue')</a>
      </div>
      @else
      <a href="/" class="btn btn--brand col-start-2 col-end-4">@lang('Homepage')</a>
      @endif
  </div>
</x-auth-page>
