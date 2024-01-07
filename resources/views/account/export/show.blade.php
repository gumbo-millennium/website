<?php declare(strict_types=1);
$data = [
    'Aangemaakt op' => $export->created_at->isoFormat('LLL'),
    'Afgerond op' => $export->completed_at?->isoFormat('LLL') ?? 'Nog niet afgerond',
    'Verloopt op' => $export->expires_at->isoFormat('LLL'),
];
?>
<x-account-page title="Inzageverzoek" activeRoute="account.export.index">
  <p class="mb-2">
      Hieronder zie je de informatie over dit inzageverzoek.
  </p>

  <p class="text-sm">
      Indien er een download beschikbaar is, kan je deze onderaan de pagina vinden.
  </p>

  <table class="min-w-full divide-y divide-gray-200 my-4">
      <tbody class="bg-white divide-y divide-gray-200">
          @foreach ($data as $key => $value)
          <tr>
              <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm leading-5 text-gray-900">
                      {{ $key }}
                  </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                  {{ $value }}
              </td>
          </tr>
          @endforeach
      </tbody>
  </table>

  {{-- Download --}}
  <div class="w-full">
      @if ($export->path && !$export->is_expired)
      <x-button style="primary" :href="route('account.export.download', [$export->id, $export->token])">
        <x-icon icon="solid/download" class="h-4 mr-2" />
          Download
      </x-button>
      @else
          <button disabled class="btn btn-small text-center" aria-label="Download niet beschikbaar">
              <x-icon icon="solid/download" class="h-8 mr-4" />
              Download
          </button>
      @endif
  </div>
</x-account-page>
