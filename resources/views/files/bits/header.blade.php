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
        @if (!empty($categories))
        <div class="files-header__topic-list">
            @foreach ($categories as $category)
            <div class="files-header__topic">
                <a
                    href="{{ route('files.category', ['slug' => $category->slug]) }}"
                    class="files-header-topic{{ $loop->last ? ' files-header-topic--static' : ''}}">
                    {{--
                    <div class="files-header-topic__icon-wrapper">
                        <div class="files-header-topic__icon files-header-topic__icon--placeholder"></div>
                    </div>
                    --}}
                    <div class="files-header-topic__name">{{ $category->title }}</div>
                </a>
            </div>
            @endforeach
        </div>
        @endif
    </div>
</div>
