@extends('layout.main')

@php
$reasonOptions = array_merge(
  array_combine(
    config('gumbo.gallery.report-reasons'),
    config('gumbo.gallery.report-reasons')
  ),
  ['other' => "Anders..."]
);
@endphp

@section('content')
<x-title>
    <h1>Foto rapporteren</h1>

    <x-slot name="subtitle">
      Voor als een foto écht niet kan
    </x-slot>
</x-title>

<div class="container">
  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
    <x-gallery.photo-tile :photo="$photo" read-only="true" />

    <form action="{{ route('gallery.photo.report') }}" method="post">
      @csrf

      <p class="text-lg">Het kan zijn dat foto's écht ongepast zijn, dan kan je ze rapporteren.</p>

      <p>Het bestuur besluit na aanleiding van je melding of de foto moet worden verwijderd en of er andere consequenties aan hangen.</p>

      <div x-data="{ reason: null }">
        <div class="mb-4">
          <label for="reason" class="text-sm">Reden van rapportage</label>

          <select name="reason" id="reason" x-model="reason">
            @foreach ($reasonOptions as $reason)
            <option
              @if(old('reason') == $reason) selected @endif
              value="{{ $reason }}">{{ $reason }}</option>
            @endforeach
          </select>
        </div>

        <div x-show="reason == 'other'" class="mb-4">
          <label for="reason-text">Andere reden opgeven</label>
          <textarea name="reason-text" id="reason-text" class="form-text w-full" rows="10">
            {{ old('reason-text') }}
          </textarea>
        </div>
      </div>

      <p>
        <strong>Let op</strong> Je kan een foto maar één keer rapporteren.
      </p>

      <button type="submit" class="btn btn-brand">Melding versturen</button>

    </form>
  </div>
</div>
@endsection
