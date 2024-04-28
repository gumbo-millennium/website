<!doctype html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <style type="text/css">{{ Vite::content('resources/css/mail.css') }}</style>
</head>
<body>
    {{-- E-mail summary --}}
    @if (!empty($summary))
    <div class="mail-hidden mail-meta">
        {{ $summary }}
    </div>

    {{-- End of e-mail summary --}}
    <div class="mail-hidden">
        &nbsp;&Atilde;&cent;&Acirc;&#128;&Acirc;&#140;&nbsp;&Atilde;&cent;&Acirc;&#128;&Acirc;&#140;&nbsp;&Atilde;&cent;&Acirc;&#128;&Acirc;&#140;&nbsp;&Atilde;&cent;&Acirc;&#128;&Acirc;&#140;&nbsp;&Atilde;&cent;&Acirc;&#128;&Acirc;&#140;&nbsp;&Atilde;&cent;&Acirc;&#128;&Acirc;&#140;&nbsp;&Atilde;&cent;&Acirc;&#128;&Acirc;&#140;&nbsp;&Atilde;&cent;&Acirc;&#128;&Acirc;&#140;&nbsp;&Atilde;&cent;&Acirc;&#128;&Acirc;&#140;&nbsp;&Atilde;&cent;&Acirc;&#128;&Acirc;&#140;&nbsp;&Atilde;&cent;&Acirc;&#128;&Acirc;&#140;&nbsp;&Atilde;&cent;&Acirc;&#128;&Acirc;&#140;&nbsp;&Atilde;&cent;&Acirc;&#128;&Acirc;&#140;&nbsp;
    </div>
    @endif

    {{-- Outer table --}}
    <table width="100%" class="mail-table mail-wrapper" cellspacing="0" cellpadding="0" border="0">
        <tbody>
            <tr>
                <td>
                    {{-- Infobar --}}
                    @include('mail::parts.infobar')

                    {{-- Header --}}
                    @include('mail::parts.header')

                    {{-- Body --}}
                    @include('mail::parts.body')

                    {{-- subcopy --}}
                    @includeWhen(!empty($subcopy), 'mail::parts.subcopy')

                    {{-- Footer --}}
                    @include('mail::parts.footer')
                </td>
            </tr>
        </tbody>
    </table>
</body>
</html>
