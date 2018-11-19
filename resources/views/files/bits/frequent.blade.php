@php
$characters = str_split('ABCDEF');
$groups = [
    'newest' => 'Recent toegevoegd',
    'popular' => 'Populair',
    'random' => 'Willekeurig'
]
@endphp

{{-- FAQ and popular --}}
<div class="files-list">
    <div class="container files-list__container">
        <h3 class="files-list__title">
            Populaire documenten
        </h3>
        <div class="row">
            @foreach($groups as $label => $name)
            <div class="col-md-4 files-list__column">
                <h4 class="files-list__column-title">{{ $name }}</h4>
                <ul class="files-list__list">
                    @forelse ($files[$label] as $file)
                    <li class="files-list__item">
                        <a
                            class="files-list__item-link"
                            href="{{ route('files.show', ['file' => $file]) }}"
                            title="{{ $file->display_title }}">
                            {{ $file->display_title }}
                        </a>
                    </li>
                    @empty
                    <li class="files-list__item text-center text-muted">
                        Geen items in deze categorie
                    </li>
                    @endforelse
                </ul>
            </div>
            @endforeach
        </div>
    </div>
</div>
