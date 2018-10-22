@php
$characters = str_split('ABCDEF');
$title = ['Recent toegevoegd', 'Veel bekeken', 'Veel gedownload']
@endphp

{{-- FAQ and popular --}}
<div class="files-search-results">
    <div class="container files-search-results__container">
        <h3 class="files-search-results__title">
            {{ $title }}
        </h3>
        <div class="files-search-results__results">
            @forelse ($results as $item)
            <div class="files-search-results__result files-search-result row">
                <img class="files-search-result__image" src="{{ $item->meta->pdf_cover }}" alt="Afbeelding van {{ $item->name }}" />
                <ul class="files-search-result__meta files-search-result__meta--desktop">
                    <li class="files-search-result__meta-item">
                        <i class="far fa-calendar" title="Geupload op"></i> {{ $item->post_date->format('d-m-Y')}}
                    </li>
                    <li class="files-search-result__meta-item">
                        <i class="far fa-file" title="Bestandsgrootte"></i> {{ $item->meta->file_size }}
                    </li>
                    <li class="files-search-result__meta-item">
                        <i class="far fa-arrow-alt-circle-down" title="Aantal downloads"></i> <span class="text-muted">n/a</span>
                    </li>
                    <li class="files-search-result__meta-item">
                        <i class="fas fa-file-signature" title="Goedgekeurd door ALV"></i> Nee
                    </li>
                </ul>
                <div class="col-sm-4 col-md-3"></div>
                <div class="col-sm-3 col-md-2"></div>
                <div class="col-sm-5 col-md-7"></div>
            </div>
            @empty

            @endforelse
        </div>
        <div class="row">
            @for ($i = 0; $i < 3; $i++)
            <div class="col-md-4 files-search-results__column">
                <h4 class="files-search-results__column-title">{{$title[$i]}}</h4>
                <ul class="files-search-results__list">
                    @for ($j = 0; $j < random_int(3,7); $j++)
                    <li class="files-search-results__item">
                        <a class="files-search-results__item-link" href="#no-op">
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
