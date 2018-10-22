@php
$characters = str_split('ABCDEF');
$title = ['Recent toegevoegd', 'Veel bekeken', 'Veel gedownload']
@endphp

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
