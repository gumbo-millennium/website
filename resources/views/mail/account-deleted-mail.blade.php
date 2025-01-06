<x-mail::message>
# Je account is verwijderd

Beste {{ $subject->first_name }},

Je hebt op {{ $subject->created_at->isoFormat('d M Y') }} een account op de Gumbo Millennium website ([gumbo-millennium.nl](https://www.gumbo-millennium.nl)) aangemaakt.
Volgens onze gegevens heb je deze voor het laatst gebruikt op {{ $subject->last_seen_at->isoFormat('d M Y') }}, en is die dus stof aan het verzamelen.

Om jouw gegevens te beschermen (en bij ons wat opslagruimte vrij te maken), is deze account verwijderd.
Je hebt hier eventueel wat mailtjes van gehad.

Mocht je later toch weer gebruik willen maken van de site, voel je dan vrij om opnieuw een account aan te maken.

Een hele fijne dag verder!

Met vriendelijke groet,
Gumbo Millennium
</x-mail::message>
