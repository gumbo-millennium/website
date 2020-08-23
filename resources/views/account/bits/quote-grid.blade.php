@php
$hasDelete = (bool) ($delete ?? false);
@endphp
@if (!empty($quotes) && count($quotes) > 0)
<table class="table-auto w-full border-collapse">
    <thead>
        <tr>
            <th>Datum</th>
            <th>Wist-je-datje</th>
            @if ($hasDelete)
            <th>&nbsp;</th>
            @endif
        </tr>
    </thead>

    <tbody>
        @foreach ($quotes as $quote)
        @php
        $dateIso = $quote->created_at->toIso8601String();
        $dateHuman = $quote->created_at->isoFormat('D MMM Y, HH:mm (z)');
        @endphp
        <tr>
            <td>
                <time datetime="{{ $dateIso }}">{{ $dateHuman }}</time>
            </td>
            <td>
                <q>{{ $quote->quote }}</q>
                –
                {{ $quote->display_name }}
            </td>
            @if ($hasDelete)
            <td>
                <button type="submit" form="quote-delete" class="appearance-none text-danger p-2" name="quote-id" value="{{ $quote->id }}">
                    @svg('solid/trash-alt', ['class' => 'icon', 'aria-label' => 'Verwijder wist-je-datje'])
                </button>
            </td>
            @endif
        </tr>
        @endforeach
    </tbody>
</table>
@else
    {{ $empty }}
@endif
