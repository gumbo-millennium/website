@extends('layout.variants.basic')

@section('basic-content-small')
{{-- Header --}}
<h1 class="login__header font-base text-4xl">Jouw <strong>inzageverzoeken</strong></h1>
<p class="text-lg text-gray-primary-2 mb-4">Bekijk welke data de site over je weet.</p>

<a href="{{ route('account.index') }}" class="w-full block mb-4">« Terug naar overzicht</a>

<p class="mb-2">
    Je kan een kopie aanvragen van alle gegevens die de site over je weet.<br />
    De data wordt in een vrije vorm opgesteld en is daarna te downloaden via deze pagina.
</p>

<p class="text-sm">
    Ideaal als je in een andere stad gaat studeren, of wil overlopen naar een andere studentenvereniging.
</p>

  <div class="-my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
    @if ($exports->isEmpty())
        <div class="text-center py-12" data-x-group="no-export-card">
            <h3 class="mt-2 text-sm font-medium text-gray-900">Geen gegevensexporten</h3>
            <p class="mt-1 text-sm text-gray-500">
                Het lijk er op dat je nog geen geen gegevensexporten hebt aangevraagd.
            </p>
        </div>
    @else
    <div class="py-8 align-middle inline-block min-w-full sm:px-6 lg:px-8" data-x-group="has-exports">
        <div class="shadow overflow-hidden border-b border-gray-200 sm:rounded-lg">
            <table class="min-w-full divide-y divide-gray-200">
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach ($exports as $export)
                    @php($iconColor = $export->is_expired ? 'text-red-200' : ($export->path ? 'text-primary-200' : 'text-gray-200'))
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-10 w-10">
                                    <x-icon icon="solid/file-archive" :class='"mx-auto h-10 $iconColor"' />
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-900">
                                        {{ $export->created_at->isoFormat('LLL') }}
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        @if ($export->is_expired)
                                            Verlopen op {{ $export->expires_at->isoFormat('LLL') }}
                                        @elseif (!$export->path)
                                            Wordt opgesteld
                                        @else
                                            Verloopt op {{ $export->expires_at->isoFormat('LLL') }}
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right">
                            <a href="{{ route('account.export.show', [$export->id, $export->token]) }}" class="text-sm no-underline font-medium text-brand-600 hover:text-brand-700">
                                Bekijken
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            @if ($exports->hasPages())
            <div class="mt-4 px-4 py-2" data-x-group="has-pages">
                <div class="mx-auto">
                    {{ $exports->links() }}
                </div>
            </div>
            @endif
        </div>
    </div>
    @endif
</div>

<!-- This example requires Tailwind CSS v2.0+ -->
<form action="{{ route('account.export.store') }}" method="POST" class="bg-white shadow sm:rounded-lg" id="request-form" data-x-group="request-form">
    @csrf
    <div class="px-4 py-5 sm:p-6">
        <h3 class="text-lg leading-6 font-medium text-gray-900">
            @lang('Request a data export')
        </h3>
        <div class="mt-2 sm:flex sm:items-start sm:justify-between">
            <div class="max-w-xl text-sm text-gray-500">
                <p>
                    @lang('The export will be downloadable for :days days after completion.', [
                        'days' => config('gumbo.export-expire-days')
                    ])
                </p>
            </div>
            <div class="mt-5 sm:mt-0 sm:ml-6 sm:flex-shrink-0 sm:flex sm:items-center">
                <button type="submit"
                    class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm font-medium rounded-md text-white bg-brand-600 hover:bg-brand-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-500 sm:text-sm">
                    Vraag aan
                </button>
            </div>
        </div>
    </div>
</form>
@endsection
