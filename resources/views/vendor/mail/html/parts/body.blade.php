<table class="mail-table mail-body" cellspacing="0" cellpadding="0" border="0" align="center">
    <tbody>
        <tr>
            <td class="mail-cell">
                <table class="mail-table mail-table--container mail-body__inner" cellspacing="0" cellpadding="0"
                    border="0" align="center">
                    <tbody>
                        <tr>
                            <td class="mail-body__inner-cell">
                                {{-- The message itself --}}
                                <table class="mail-body__inner-table" cellspacing="0" cellpadding="0"
                                    border="0" align="center">
                                    <tbody>
                                        {{-- Image --}}
                                        @if (!empty($mailImage))
                                        <tr>
                                            <td>
                                                <img class="mail-body__title-card" src="{{ $mailImage }}" alt="">
                                            </td>
                                        </tr>
                                        @endif

                                        {{-- Body --}}
                                        <tr>
                                            <td>
                                                <div class="mail-body__message {{ empty($mailImage) ? '0mail-body__message--single' : '' }}">
                                                    {{-- Markdown --}}
                                                    {{ Illuminate\Mail\Markdown::parse($slot) }}

                                                    @if ($html ?? null)
                                                    {{-- Non-markdown --}}
                                                    {{ $html }}
                                                    @endif

                                                    {{-- Markdown greeting --}}
                                                    @if ($greeting ?? null)
                                                    {{ Illuminate\Mail\Markdown::parse($greeting) }}
                                                    @endif

                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </td>
        </tr>
    </tbody>
</table>
