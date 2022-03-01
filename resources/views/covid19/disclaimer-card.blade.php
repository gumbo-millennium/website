<article class="col w-full flex items-stretch flex-none md:w-1/2 mb-8">
  <div class="card">
    <div class="card__figure card__figure--alleen-samen hidden md:block" role="none">
      <picture class="card__figure-wrapper">
          <source type="image/webp" srcset="/images/alleen-samen.webp, /images/alleen-samen@2x.webp 2x, /images/alleen-samen@4x.webp 4x" />
          <source type="image/png" src="/images/alleen-samen.png" />
          <img src="/images/alleen-samen.png"
            alt="Alleen Samen krijgen we corona onder controle" class="h-16 mx-auto">
      </picture>
    </div>

    <div class="card__body">
      <h2 class="card__body-title">Officiële informatie</h2>
      <p class="card__body-content">
        De informatie op deze pagina's kan verouderd zijn en is enkel
        een aanvulling op de officiele informatie verstrekt door de
        rijksoverheid.
        @include('covid19.block', ['outbound' => 1])
      </p>
    </div>
  </div>
</article>
