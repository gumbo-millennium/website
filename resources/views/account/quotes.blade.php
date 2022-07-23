<x-account-page>
  <p class="leading-loose mb-2">
      Hieronder zie je de nog-te-versturen wist-je-datjes (waar je eventueel zelfcensuur op kan loslaten), en
      een beperkte set verstuurde wist-je-datjes.
  </p>

  {{-- Deletion form --}}
  <form name="quote-delete" id="quote-delete" class="hidden" aria-hidden="true" action="{{ route('account.quotes.delete') }}" method="POST">
      @csrf
      @method('DELETE')
  </form>

  {{-- Pending quotes --}}
  <h3 class="font-title text-2xl">Te-verzenden wist-je-datjes</h3>
  <p class="mb-4">Deze wist-je-datjes moeten nog verstuurd worden. Hier kàn je nog een potje zelfcensuur op loslaten</p>

  <x-account.quote-grid :delete="true" :quotes="$unsent">
      <div class="py-16 px-4 text-center">
          <h3 class="text-title text-center">Geen wist-je-datjes</h3>
          <p class="text-gray-600">Je hebt nog geen wist-je-datjes ingestuurd, of ze zijn allemaal al doorgestuurd.</p>
      </div>
  </x-account.quote-grid>

  <hr class="my-8 border-gray-300" />

  {{-- Sent quotes --}}
  <h3 class="font-title text-2xl">Verzonden wist-je-datjes</h3>
  <p class="mb-4">Deze wist-je-datjes zijn doorgestuurd naar de Gumbode. Oude wist-je-datjes kunnen verwijderd worden.</p>

  <x-account.quote-grid :delete="false" :quotes="$sent">
      <div class="py-16 px-4 text-center">
          <h3 class="text-title text-center">Geen wist-je-datjes</h3>
          <p class="text-gray-600">Er zijn geen wist-je-datjes van jou doorgestuurd naar de Gumbode, of ze zijn verwijderd.</p>
      </div>
  </x-account.quote-grid>
</x-account-page>
