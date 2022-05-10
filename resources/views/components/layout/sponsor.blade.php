@if (Sponsors::hasSponsor() && $sponsor = Sponsors::getSponsor())
    @if ($sponsor->is_classic)
      <x-layout.sponsors.classic :sponsor="$sponsor" />
    @else
      <x-layout.sponsors.modern :sponsor="$sponsor" />
    @endif
@endif
