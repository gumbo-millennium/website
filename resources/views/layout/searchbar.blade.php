@php
$suggestions = [
    'activiteiten',
    'nieuwsartikelen',
    'pagina\'s'
];
$user = auth()->user();
if ($user && $user->can('viewPublic', App\Models\File::class)) {
    array_unshift($suggestions, 'bestanden');
}

$lastSuggestion = array_pop($suggestions);
$searchPlaceholder = sprintf('Doorzoek %s en %s', implode(', ', $suggestions), $lastSuggestion);
@endphp
<form class="searchbar" input="{{ route('search-form') }}" method="GET">
    <div class="container searchbar__container">
        <button class="searchbar__close" aria-label="Zoeken sluiten">
            <span aria-hidden="true">×</span>
        </button>
        <div class="searchbar__form-container">
            <div class="searchbar__input-wrapper">
                <input class="searchbar__input-search" type="text" name="q" placeholder="{{ $searchPlaceholder }}" value="{{ old('q') }}" />
                <input class="searchbar__input-submit" type="submit" value="Zoeken" />
            </div>

            <h2 class="searchbar__quick-header">Most recent</h2>

        </div>
    </div>
</form>
