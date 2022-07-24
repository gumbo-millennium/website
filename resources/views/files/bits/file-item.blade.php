@php
// Get base values
$fileDate = null;
$fileName = $file->name;

// Get matches
if (preg_match('/^(\d{4}-\d{2}-\d{2})([^a-z0-9])(.+)$/i', $file->name, $matches)) {
    $fileDate = $matches[1];
    $separator = $matches[2];
    $fileName = str_replace($separator, ' ', $matches[3]);
}
@endphp

<div class="file-set__item">
    {{-- Get title --}}
    <a href="{{ route('files.download-single', ['media' => $file]) }}" class="file-set__item-title">
        @if ($fileDate)
        <span class="inline-block text-gray-500 mr-1">{{ $fileDate }}</span>
        @endif
        {{ $fileName }}
    </a>

    {{-- Meta --}}
    <div class="file-set__item-meta">
        <p class="file-set__item-meta-item">{{ Str::filesize($file->size) }}</p>
        <p class="file-set__item-meta-item">{{ $file->downloads_count }} downloads</p>
    </div>
</div>
