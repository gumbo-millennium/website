@props([
  'photo',
  'tools' => false,
])
<div class="rounded shadow grid grid-cols-2" x-data="{ deleted: false, hidden: @json(!$photo->is_visible) }">
  <div class="relative h-full">
    <div class="relative h-full" x-bind:class="deleted ? 'filter grayscale contrast-50' : (hidden ? 'filter contrast-50' : '')">
      @if ($url = (string) image_asset($photo->path)->preset('tile'))
      <picture>
        <img src="{{ $url }}" class="max-h-[400px] h-full w-full object-cover rounded" />
      </picture>
      @else
      <x-empty-image class="h-64 w-full rounded-t" />
      @endif
    </div>
    <div class="absolute inset-0 flex items-center justify-center">
      <x-icon icon="solid/trash-alt" x-cloak class="h-12 text-red-600" x-show="deleted == true" />
      <x-icon icon="solid/eye-slash" x-cloak class="h-12 text-red-primary-1" x-show="deleted != true && hidden == true" />
    </div>
  </div>

  <div class="flex-grow flex flex-col items-strech w-full items-center justify-center" x-show="deleted" x-cloak>
    <p class="text-lg text-medium text-gray-700">Verwijderen</p>
    <p class="text-sm mb-4">Foto wordt verwijderd na het opslaan</p>

    <button class="btn btn-small m-0" @click.prevent="deleted = false">
      niet verwijderen
    </button>
  </div>

  <div class="p-4 flex flex-col space-y-4" x-show=!deleted>
    <div>
      <h3 class="font-title text-lg text-ellipsis overflow-hidden whitespace-nowrap">{{ $photo->name }}</h3>
    </div>

    <dl class="grid grid-cols-3 text-sm gap-y-2 my-2">
      <dt>Auteur</dt>
      <dd class="col-span-2">{{ $photo->user?->name ?? 'onbekend' }}</dd>

      <dt>Gemaakt op</dt>
      <dd class="col-span-2">{{ $photo->taken_at?->isoFormat('d MMM YYYY') ?? 'onbekend' }}</dd>

      <dt>Zichtbaarheid</dt>
      <dd class="col-span-2">{{ $photo->is_visible ? 'zichtbaar' : 'verborgen' }}</dd>
    </dl>

    <div>
      <label for="phpto.{{ $photo->id }}.description" class="text-sm block">Omschrijving</label>
      <textarea name="photo[{{ $photo->id }}][description]" id="phpto.{{ $photo->id }}.description" rows="3" class="w-full">{{ $photo->description }}</textarea>
    </div>

    <div class="flex-grow"></div>

    <div class="flex flex-row gap-x-2">
      <button
        class="btn btn-small btn-secondary m-0 flex-grow"
        @click.prevent="hidden = !hidden"
        type="button"
        x-text="hidden ? 'Tonen' : 'Verbergen'"
        aria-label="Wissel zichtbaarheid"
      ></button>

      <button class="btn btn-small btn-outline-danger m-0" @click.prevent="deleted = true" type="button" aria-label="Verwijder foto">
        <x-icon icon="solid/times" class="h-4 mr-2" />
      </button>
    </div>
  </div>

  <input type="hidden" name="photo[{{ $photo->id }}][visible]" x-bind:value="hidden ? 'hidden' : 'visible'">
  <input type="hidden" name="photo[{{ $photo->id }}][delete]" x-bind:value="deleted ? 'delete' : ''">
</div>
