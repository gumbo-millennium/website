@php
    $publishIcon = $file->public ? 'fa-check-square' : 'fa-square';
    $publishLabel = $file->public ? 'verbergen' : 'publiceren';
@endphp
<tr>
    <td>
        <a href="{{ route('admin.files.show', ['file' => $file]) }}">
            {{ $file->display_title }}
        </a>

        {{-- Forms --}}
        <form id="file-public-{{ $file->id }}" class="d-none" action="{{ route('admin.files.publish', [
            'file' => $file,
            'category' => $category
        ]) }}" method="POST">
            @method('PATCH')
            @csrf
            <input type="hidden" name="public" value="{{ $file->public ? '0' : '1' }}" />
        </form>
        <form id="file-delete-{{ $file->id }}" class="d-none" action="{{ route('admin.files.delete', [
            'file' => $file,
            'category' => $category
        ]) }}" method="POST">
            @method('DELETE')
            @csrf
        </form>
    </td>
    <td>
        {{ optional($file->owner)->name ?? '–' }}
    </td>
    <td>
        @foreach ($file->processing_status as $status)
        <span class="badge badge-pill badge-primary">{{ $status }}</span>
        @endforeach
    </td>
    <td class="text-center" style="width: 12rem;">
        <div class="dropdown">
            <a
                href="#"
                class="btn btn-secondary-outline dropdown-toggle"
                role="button"
                data-boundary="window"
                data-toggle="dropdown"
                aria-haspopup="true"
                aria-expanded="false"
                id="file-actions-{{ $file->slug }}"
            >Acties</a>
        <div class="dropdown-menu" role="menu" aria-labelledby="file-actions-{{ $file->slug }}">
                {{-- view file link --}}
                @if ($file->public)
                <a href="{{ $file->url }}" class="dropdown-item" title="bekijk op site">
                    <i class="fas fa-external-link-alt fa-fw"></i>
                    bekijk op site
                </a>
                @endif

                {{-- download file link --}}
                @if (!$file->broken)
                <a href="{{ route('admin.files.download', ['file' => $file]) }}" class="dropdown-item" title="dowloaden">
                    <i class="fas fa-download fa-fw"></i>
                    download
                </a>
                @endif

                {{-- download file link --}}
                @if (!$file->hasState(\App\File::STATE_PDFA))
                <a href="{{ route('admin.files.pdfa', ['file' => $file]) }}" class="dropdown-item" title="omzetten naar PDF/A">
                    <i class="fas fa-box fa-fw"></i>
                    omzetten naar PDF/A
                </a>
                @endif

                <div class="dropdown-header">Zichtbaarheid</div>

                {{-- Publish button --}}
                @can('publish', $file)
                <button type="submit" form="file-public-{{ $file->id }}" class="dropdown-item" title="{{ $publishLabel }}">
                    <i class="far {{ $publishIcon }} fa-fw"></i>
                    Gepubliceerd
                </button>
                @endcan

                <div class="dropdown-header">Administratie</div>

                {{-- Update link --}}
                @can('update', $file)
                <a href="{{ route('admin.files.edit', ['file' => $file]) }}" class="dropdown-item" title="bewerken">
                    <i class="fas fa-pencil-alt fa-fw"></i>
                    bewerken
                </a>
                @endcan

                {{-- Delete link --}}
                @can('delete', $file)
                <a href="{{ route('admin.files.edit', ['file' => $file]) }}" class="dropdown-item" title="verwijderen">
                    <i class="fas fa-trash-alt fa-fw"></i>
                    verwijderen
                </a>
                @endcan
            </div>
        </div>
    </td>
</tr>
