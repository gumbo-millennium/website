<x-auth-page title="Login">
  <x-sections.transparent-header title="Voorwaarden" subtitle="Even om duidelijk aan te geven hoe we jouw gegevens gebruiken" />
    <div class="mb-8">
        <p>
            Bij Gumbo Millennium hechten wij veel waarden aan de privacy van onze
            leden en bezoekers.<br />
            Daarom hebben wij een privacybeleid opgesteld met als oog om de
            informatie over onze leden zo goed mogelijk te beschermen.
        </p>
        <div class="my-4 p-4 border border-brand-500 rounded">
            <p>
                <a target="_blank" href="/privacy-policy">Lees ons privacybeleid</a>
                (opent in een nieuw tabblad).
            </p>
        </div>
    </div>

    <p class="mb-8 text-gray-600">
        Hierbij een kort overzicht van de gegevens die direct worden gedeeld zodra je akkoord gaat.
    </p>

    @forelse ($companies as $company)
    <details class="mb-8" role="listitem">
        {{-- Company name --}}
        <summary>
          <h3 class="inline text-lg">{{ $company['name'] }}</h3>
        </summary>

        {{-- Intro --}}
        <p class="mb-2">
            Gumbo werkt samen met {{ $company['name'] }} voor {{ Arr::implode($company['purposes']) }}. Hiervoor verstrekken
            wij de volgende gegevens:
        </p>

        {{-- Data shared --}}
        <ul class="list-disc ml-4 mb-2">
            @foreach ($company['data'] as $data)
            <li>{{ $data }}</li>
            @endforeach
        </ul>

        {{-- There might be links --}}
        @if (!empty($company['privacy-urls']))
        <p class="mb-2">
            Meer lezen:
            @foreach ($company['privacy-urls'] as $label => $url)
            <a href="{{ $url }}" rel="noopener nofollow" target="_blank">{{ $label }}</a>,
            @endforeach
        </p>
        @endif
    </details>
    @empty
    @endforelse


    {{-- Render form --}}
    {!! form($form, ['class' => 'form']) !!}
  </div>

</x-auth-page>

