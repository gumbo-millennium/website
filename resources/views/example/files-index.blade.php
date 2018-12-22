@php
$characters = str_split('ABCDEF');
$title = ['Recent toegevoegd', 'Veel bekeken', 'Veel gedownload']
@endphp

{{-- Page header --}}
<div class="files-header">
    <form class="container files-header__container">
        {{-- Page title --}}
        <h1 class="files-header__title">Documenten</h1>

        {{-- Search field --}}
        <div class="files-header__search">
            <label role="presentation" for="files-search" class="files-header__search-icon">
                <i class="fas fa-search fa-fw" aria-label="Search icon"></i>
            </label>
            <input type="search" class="files-header__search-field" placeholder="Zoeken naar bestanden" id="files-search">
        </div>

        {{-- Common topics (x4) --}}
        @if (!empty($searchTopics))
        <div class="files-header__topic-list">
            @foreach ($searchTopics as $topic)
            <div class="files-header__topic">
                <a href="#find-by={{ str_slug($topic) }}" class="files-header-topic{{ $loop->last ? ' files-header-topic--static' : ''}}">
                    {{-- <div class="files-header-topic__icon-wrapper">
                        <div class="files-header-topic__icon files-header-topic__icon--placeholder"></div>
                    </div> --}}
                    <div class="files-header-topic__name">{{ $topic }}</div>
                </a>
            </div>
            @endforeach
        </div>
        @endif
    </div>
</div>

{{-- FAQ and popular --}}
<div class="files-list">
    <div class="container files-list__container">
        <h3 class="files-list__title">
            Populaire documenten
        </h3>
        <div class="row">
            @for ($i = 0; $i < 3; $i++)
            <div class="col-md-4 files-list__column">
                <h4 class="files-list__column-title">{{$title[$i]}}</h4>
                <ul class="files-list__list">
                    @for ($j = 0; $j < random_int(3,7); $j++)
                    <li class="files-list__item">
                        <a class="files-list__item-link" href="#no-op">
                            {{str_slug("Notulen ALV {$characters[$i]} {$j}")}}.pdf
                        </a>
                    </li>
                    @endfor
                </ul>
            </div>
            @endfor
        </div>
    </div>
</div>
