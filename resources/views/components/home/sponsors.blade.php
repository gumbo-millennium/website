@if ($sponsors->count() > 2)
@php(Sponsors::hideSponsor())
<div class="bg-white">
  <div class="max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
    <div class="grid grid-cols-2 gap-8 md:grid-cols-6 lg:grid-cols-5">
      @foreach ($sponsors->take(5) as $sponsor)
      <a href="{{ route('sponsors.link', $sponsor) }}" target="_blank" rel="noopener nofollow" class="col-span-1 flex justify-center md:col-span-2 lg:col-span-1">
        {{ Sponsors::toSvg($sponsor, ['class' => 'h-12']) }}
      </a>
      @endforeach
    </div>
  </div>
</div>
@endif
