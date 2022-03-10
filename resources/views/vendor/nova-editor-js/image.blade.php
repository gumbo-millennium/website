<div class="editor-js-block">
  <div class="container container--lg">
    <div class="editor-js-image {{ $classes }}">
      <picture class="max-h-[960px] w-full object-contain">
        <source type="image/webp" src="
          {{ image_asset($file['url'])->width(1280)->height(960)->webp() }} 1280w,
          {{ image_asset($file['url'])->width(768)->height(576)->webp() }} 768w,
          {{ image_asset($file['url'])->width(640)->height(480)->webp() }}" />
        <source type="image/jpeg" src="
          {{ image_asset($file['url'])->width(1280)->height(960)->jpeg() }} 1280w,
          {{ image_asset($file['url'])->width(640)->height(480)->jpeg() }}" />
        <img src="{{ image_asset($file['url'])->width(640)->height(480)->jpeg() }}" alt="{{ $caption }}" class="rounded-lg overflow-hidden" />
      </picture>
      @if (!empty($caption))
          <div class="block px-4 py-2 text-gray-700 text-sm">{{ $caption }}</div>
      @endif
    </div>
</div>
