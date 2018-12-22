<table border="0" colspan="0" rowspan="2">
    <tbody>
        <tr>
            <th scope="row">Voornaam</th>
            <td>{{ $joinData['first_name'] }}</td>
        </tr>
        <tr>
            <th scope="row">Tussenvoegsel</th>
            <td>{{ $joinData['insert'] }}</td>
        </tr>
        <tr>
            <th scope="row">Achternaam</th>
            <td>{{ $joinData['last_name'] }}</td>
        </tr>
        <tr>
            <td colspan="2">
                <hr />
            </td>
        </tr>
        <tr>
            <th scope="row" rowspan="3">Adres</th>
            <td>{{ $joinData['street'] }} {{ $joinData['number'] }}</td>
        </tr>
        <tr>
            <td>{{ $joinData['zipcode'] }} {{ $joinData['city'] }}</td>
        </tr>
        <tr>
            <td>{{ strtoupper($joinData['country']) }}</td>
        </tr>
        <tr>
            <th scope="row">E-mail adres</th>
            <td>{{ $joinData['email'] }}</td>
        </tr>
        <tr>
            <th scope="row">Telefoonnummer</th>
            <td>{{ $joinData['phone'] }}</td>
        </tr>
        <tr>
            <th scope="row">Geboortedatum</th>
            <td>{{ $joinData['date-of-birth'] }}</td>
        </tr>
        <tr>
            <td scope="row">Lidtype</th>
            @if ($joinData['windesheim-student'] ?? false)
            <td><strong>Lid</strong> - Je bent een student aan Hogeschool Windesheim</td>
            @else
            <td><strong>Begunstiger</strong> - Je bent geen student aan Hogeschool Windesheim</td>
            @endif
        </tr>
        <tr>
            <td colspan="2">
                <hr />
            </td>
        </tr>
        <tr>
            <th scope="row">Statuen en privacy<wbr />beleid</th>
            <td>
                Je bent akkoord gegaan met het privacybeleid en de statuten van Gumbo Millennium.
            </td>
        </tr>
        <tr>
            <th scope="row">Gumbode</th>
            <td>
                {{ $joinData['accept-newsletter'] ? 'Ja' : 'Nee' }}
            </td>
        </tr>
    </tbody>
</table>
