<x-mail::message markdown>
  <h2>Je account is verwijderd</h2>

  <p>
    Beste {{ $subject->first_name }},
  </p>

  <p>
    Je hebt op {{ $subject->created_at->isoFormat('D MMMM YYYY') }} een account op de Gumbo Millennium website aangemaakt.<br/>
    Volgens onze gegevens heb je deze voor het laatst gebruikt op {{ $subject->last_seen_at->isoFormat('D MMMM YYYY, hh:mm') }}, en is die dus stof aan het verzamelen.
  </p>

  <p>
    Om jouw gegevens te beschermen (en bij ons wat opslagruimte vrij te maken), is deze account verwijderd.<br/>
    Je hebt hier eventueel wat mailtjes van gehad.
  </p>

  <p>
    Mocht je later toch weer gebruik willen maken van de site, voel je dan vrij om opnieuw een account aan te maken.
  </p>

  <p>
    Een hele fijne dag verder!
  </p>

  <p>
    Met vriendelijke groet,<br/>
    Gumbo Millennium
  </p>

  <x-slot:subcopy>
    Dit is een eenmalig bericht van de Gumbo Millennium website.
    Je gegevens zijn na deze mail permanent verwijderd, dus je ontvangt nu niks meer.
  </x-slot:subcopy>
</x-mail::message>
