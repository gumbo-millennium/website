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
                                                <div class="mail-body__message {{ empty($mailImage) ? 'mail-body__message--single' : '' }}">
                                                    {{ Illuminate\Mail\Markdown::parse($slot) }}
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
