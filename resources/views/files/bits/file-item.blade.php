<div class="file-set__item">
    {{-- Get title --}}
    <a href="{{ route('files.download-single', ['media' => $file]) }}" class="file-set__item-title">
        {{ $file->name }}
    </a>

    {{-- Meta --}}
    <div class="file-set__item-meta">
        <p class="file-set__item-meta-item">{{ Str::filesize($file->size) }}</p>
        <p class="file-set__item-meta-item">{{ $file->downloads_count }} downloads</p>
    </div>
</div>
