<table class="mail-table mail-icon-row" align="center" width="100%" cellpadding="0" cellspacing="0" role="presentation">
    <tr>
        <td
            class="mail-table__cell mail-icon-row__cell mail-icon-row__cell--icon"
            align="center"
            valign="top"
        >
            <table class="mail-table mail-icon-row__icon-table" align="center" cellpadding="16" cellspacing="0" role="presentation" width="160">
                <tr>
                    <td class="mail-table__cell mail-icon-row__icon-cell">
                        <img src="{{ $icon ?? Vite::image('images/icon-box.png') }}" class="mail-icon-row__icon" height="64"
                            alt="{{ $iconAlt ?? 'Icon' }}" />
                    </td>
                </tr>
            </table>
        </td>
        <td class="mail-table__cell mail-icon-row__cell mail-icon-row__cell-main">
            <table class="mail-table mail-icon-row__body-table" align="center" cellpadding="16" cellspacing="0" role="presentation"
                width="100%">
                <tr>
                    <td class="mail-table__cell mail-icon-row__body-cell">
                        @if (!empty($title))
                            <h4 class="mail-icon-row__body-title">{{ $title }}</h4>
                        @endif

                        {{ $slot }}
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
