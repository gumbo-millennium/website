<table class="mail-table mail-subcopy" cellspacing="0" cellpadding="0" border="0" align="center">
    <tbody>
        <tr>
            <td class="mail-cell">
                <table class="mail-table mail-table--container mail-subcopy__inner" cellspacing="0" cellpadding="0"
                    border="0" align="center">
                    <tbody>
                        <tr>
                            <td class="mail-subcopy__inner-cell">
                                {{ Illuminate\Mail\Markdown::parse($subcopy) }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </td>
        </tr>
    </tbody>
</table>
