<table class="action" align="center" width="100%" cellpadding="0" cellspacing="0" role="presentation">
    <tr>
        <td align="center">
            <table width="100%" border="0" cellpadding="0" cellspacing="0" role="presentation">
                <tr>
                    <td align="center">
                        {{-- This is illegal, but it's email --}}
                        <a href="{{ $url }}" class="btn btn--{{ $color ?? 'brand' }} mail-button" target="_blank">
                            <div class="mail-button__inner">{{ $slot }}</div>
                        </a>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
