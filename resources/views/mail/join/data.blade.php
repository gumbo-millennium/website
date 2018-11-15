<table border="0" colspan="0" rowspan="2">
    <tbody>
        <tr>
            <th scope="row">Voornaam</th>
            <td>{{ $user->first_name }}</td>
        </tr>
        <tr>
            <th scope="row">Tussenvoegsel</th>
            <td>{{ $user->insert }}</td>
        </tr>
        <tr>
            <th scope="row">Achternaam</th>
            <td>{{ $user->last_name }}</td>
        </tr>
        <tr>
            <td colspan="2">
                <hr />
            </td>
        </tr>
        <tr>
            <th scope="row" rowspan="3">Adres</th>
            <td>{{ $request->street }} {{ $request->number }}</td>
        </tr>
        <tr>
            <td>{{ $request->zipcode }} {{ $request->city }}</td>
        </tr>
        <tr>
            <td>{{ strtoupper($request->country) }}</td>
        </tr>
        <tr>
            <th scope="row">E-mail adres</th>
            <td>{{ $user->email }}</td>
        </tr>
        <tr>
            <th scope="row">Telefoonnummer</th>
            <td>{{ $request->phone }}</td>
        </tr>
        <tr>
            <th scope="row">Geboortedatum</th>
            <td>{{ $request->date_of_birth->format('d-m-Y') }}</td>
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
                @if ($request->accept_newsletter) Je hebt aangegeven de Gumbode te willen ontvangen. @else Je hebt aangegeven de Gumbode
                <strong>niet</strong> te willen ontvangen. @endif
            </td>
        </tr>
    </tbody>
</table>
