<x-mail::layout>
<x-slot name="header">
    Welkom bij de kitchen sink
</x-slot>

Beste {{ $recipient->first_name }},

Lorem ipsum dolor sit amet consectetur adipisicing elit. Magnam exercitationem,
doloribus officiis rerum recusandae temporibus fugiat repellendus debitis
maiores laudantium accusantium vel hic asperiores tempora. Neque possimus quo
quam adipisci!

<x-mail::promotion>
    # The Holy Hand Grenade of Antioch

    And Saint Attila raised the hand grenade up on high, saying, "O Lord, bless this
    thy hand grenade, that with it thou mayst blow thine enemies to tiny bits, in
    thy mercy." And the Lord did grin. And the people did feast upon the lambs, and
    sloths, and carp, and anchovies, and orangutans, and breakfast cereals, and
    fruit bats, and large chu–
</x-mail::promotion>

<x-mail::button url="https://www.youtube.com/watch?v=dQw4w9WgXcQ">
    Buy Now
</x-mail::button>

Lorem ipsum dolor sit amet consectetur adipisicing elit. Ea reprehenderit quasi
voluptate aspernatur natus dolorem doloribus, modi aperiam accusantium adipisci
tempora autem, optio eveniet ducimus repellendus quaerat eligendi illum minus?

<x-mail::panel>
    # Lorem ipsum, dolor sit amet consectetur adipisicing elit.

    Adipisci est consequatur quod? Labore, dolores officiis voluptate cupiditate
    dolor sapiente cumque qui iure tempore possimus obcaecati incidunt harum a
    fugiat voluptas.
</x-mail::panel>

{{-- Subcopy --}}
<x-slot name="subcopy">
    Listen, strange women lyin' in ponds distributin' swords is no basis for a
    system of government. Supreme executive power derives from a mandate from the
    masses, not from some farcical aquatic ceremony.
</x-slot>
</x-mail::layout>
