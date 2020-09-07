@if (!empty($outbound))
    <a href="https://www.rijksoverheid.nl/onderwerpen/coronavirus-covid-19" rel="friend" target="__blank" class="covid-btn">
      @svg('solid/external-link-alt', 'mr-2')
      <span>Meer informatie</span>
    </a>
@else
    <a href="{{ url('/coronavirus') }}" class="covid-btn">
      <span>Meer informatie</span>
    </a>
@endif
