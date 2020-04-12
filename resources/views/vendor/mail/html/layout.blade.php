<!doctype html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <style type="text/css">{{ mix_file('/mail.css') }}</style>
</head>
<body>
    <table class="mail" width="100%" cellpadding="0" cellspacing="0" role="presentation">
        <tr>
            <td align="center">
                <table class="mail__content" width="100%" cellpadding="0" cellspacing="0" role="presentation">
                    {{-- E-mail header --}}
                    <tr>
                        <td>
                            <table class="mail-header" align="center" width="570" cellpadding="0" cellspacing="0" role="presentation">
                                <tr>
                                    <td class="mail-header__inner content-cell content-cell--header" align="center">
                                        <a href="{{ url("/") }}" class="mail-header__link">
                                            <img class="mail-header__logo" height="60" src="{{ mix('images/mail/logo.png') }}"
                                                alt="Gumbo Millennium" border="0" data-auto-embed />
                                        </a>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Email Body -->
                    <tr>
                        <td class="mail-body" width="100%" cellpadding="0" cellspacing="0">
                            <table class="mail-body__inner" align="center" width="570" cellpadding="0" cellspacing="0" role="presentation">
                                {{-- Body header --}}
                                @isset ($header)
                                <tr>
                                    <td class="mail-body__content mail-body__content--header">
                                        {{ $header }}
                                    </td>
                                </tr>
                                @endisset

                                <!-- Body content -->
                                <tr>
                                    <td class="mail-body__content mail-body__content--main">
                                        {{ Illuminate\Mail\Markdown::parse($slot) }}
                                    </td>
                                </tr>

                                {{-- Body subcopy --}}
                                @isset ($subcopy)
                                <tr>
                                    <td class="mail-body__content mail-body__content--subcopy">
                                        {{ Illuminate\Mail\Markdown::parse($subcopy) }}
                                    </td>
                                </tr>
                                @endisset
                            </table>
                        </td>
                    </tr>

                    {{-- Email footer --}}
                    <tr>
                        <td>
                            <table class="mail-footer" align="center" width="570" cellpadding="0" cellspacing="0" role="presentation">
                                <tr>
                                    <td class="mail-footer__inner content-cell content-cell--footer" align="center">
                                        <p class="mail-footer__text">© {{ today()->year }} Gumbo Millennium. Alle rechten voorbehouden.</p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
