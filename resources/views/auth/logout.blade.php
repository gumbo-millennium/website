<x-auth-page title="Uitgelogd">
  <x-sections.transparent-header title="Okay doei"
    subtitle="Je bent succesvol uitgelogd" />

  <div class="text-center">
    <div class="flex space-x-4 justify-center">
      @if ($next ?? null)
        <a href="/" class="btn">@lang('Homepage')</a>
        <a href="{{ $next }}" class="btn btn--brand">@lang('Continue')</a>
      @else
        <a href="/" class="btn btn--brand col-start-2 col-end-4">@lang('Homepage')</a>
      @endif
    </div>
  </div>
</x-auth-page>
