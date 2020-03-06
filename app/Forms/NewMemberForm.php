<?php

declare(strict_types=1);

namespace App\Forms;

use App\Forms\Traits\UseTemplateStrings;
use App\Rules\PhoneNumber;
use Kris\LaravelFormBuilder\Form;

class NewMemberForm extends Form
{
    use UseTemplateStrings;

    /**
     * Builds the form
     */
    public function buildForm()
    {
        $dummyName = $this->getTemplateName();

        // Name
        $this
            ->add('first-name', 'text', [
                'label' => 'Voornaam',
                'rules' => 'required|string|min:2',
                'attr' => [
                    'autocomplete' => 'given-name',
                    'autofocus' => true,
                    'placeholder' => $dummyName[0]
                ],
            ])
            ->add('insert', 'text', [
                'label' => 'Tussenvoegsel',
                'rules' => 'nullable|string|min:2',
                'attr' => [
                    'autocomplete' => 'additional-name',
                    'placeholder' => $dummyName[1]
                ],
            ])
            ->add('last-name', 'text', [
                'label' => 'Achternaam',
                'rules' => 'required|string|min:2',
                'attr' => [
                    'autocomplete' => 'family-name',
                    'placeholder' => $dummyName[2]
                ],
            ]);

        // Contact details
        $this
            ->add('email', 'email', [
                'label' => 'E-mailadres',
                'rules' => 'required|email',
                'attr' => [
                    'autocomplete' => 'email',
                    'placeholder' => $dummyName[3]
                ],
            ])
            ->add('phone', 'tel', [
                'label' => 'Telefoonnummer',
                'rules' => ['required', new PhoneNumber('NL')],
                'attr' => [
                    'autocomplete' => 'tel',
                    'placeholder' => '038 845 0100'
                ],
                'help_block' => [
                    'text' => <<<'TEXT'
                    Geen Nederlands nummer? Typ dan een internationaal nummer in (zoals +49 201 567 890).
                    TEXT
                ],
            ]);

        // Personal info
        $this
            ->add('date-of-birth', 'date', [
                'label' => 'Geboortedatum',
                'rules' => ['required', sprintf('before:%s', today()->subYear(17)->format('Y-m-d'))],
                'attr' => [
                    'autocomplete' => 'bday'
                ],
                'help_block' => [
                    'text' => <<<'TEXT'
                    Om je aan te melden voor Gumbo Millennium via de website
                    moet je minimaal 17 jaar oud zijn.
                    TEXT
                ],
            ])
            ->add('gender', 'choice', [
                'label' => 'Geslacht',
                'rules' => 'required',
                'choices' => [
                    'man' => 'Man',
                    'vrouw' => 'Vrouw'
                ],
                'help_block' => [
                    'text' => <<<'TEXT'
                    Wil je liever een andere notatie vast laten leggen? Stuur
                    dan even een mailtje naar bestuur@gumbo-millennium.nl.
                    TEXT
                ],
            ]);

        // Address
        $this
            ->add('street', 'text', [
                'label' => 'Straatnaam',
                'rules' => 'required',
                'attr' => [
                    'placeholder' => 'Campus',
                ]
            ])
            ->add('number', 'text', [
                'label' => 'Huisnummer',
                'rules' => 'required',
                'attr' => [
                    'placeholder' => '2-6',
                ]
            ])
            ->add('postal-code', 'text', [
                'label' => 'Postcode',
                'rules' => 'required',
                'attr' => [
                    'autocomplete' => 'postal-code',
                    'placeholder' => '8017 CA'
                ]
            ])
            ->add('city', 'text', [
                'label' => 'Plaats',
                'rules' => 'required',
                'attr' => [
                    'autocomplete' => 'address-level2',
                    'placeholder' => 'Zwolle'
                ]
            ])
            ->add('country', 'text', [
                'label' => 'Land',
                'default_value' => 'Nederland',
                'attr' => [
                    'autocomplete' => 'country-name',
                    'placeholder' => 'Indien niet Nederland'
                ]
            ]);

        // Extra fields
        $this
            ->add('is-student', 'checkbox', [
                'label' => 'Windesheim student',
                'help_block' => [
                    'text' => <<<'TEXT'
                    Binnen Gumbo hebben wij twee lidmaatschapsvormen: leden en
                    begunstigers. Om te bepalen welke vorm voor jou mogelijk
                    is, moeten we weten of je op Windesheim studeert.
                    TEXT
                ]
            ])
            ->add('is-newsletter', 'checkbox', [
                'label' => 'Nieuwsbrief',
                'help_block' => [
                    'text' => <<<'TEXT'
                    Als je dat wilt, kan je elke maand de Gumbode ontvangen,
                    met daarin een samenvatting van de maand, leuke verhaaltjes
                    en de gekste uitspraken van onze leden.
                    TEXT
                ]
            ]);

        // Terms
        $this
            ->add('accept-terms', 'checkbox', [
                'label' => 'Ik accepteer het Gumbo Millennium Privacybeleid',
                'rules' => 'required|accepted',
                'help_block' => [
                    'text' => sprintf(
                        <<<'HTML'
                        <a href="%s" target="_blank">Lees het Privacybeleid</a>
                        (opent in een nieuw tabblad).
                        HTML,
                        url('/privacy-policy')
                    )
                ]
            ]);

        // Submit button
        $this
            ->add('submit', 'submit', [
                'label' => 'Aanmelden'
            ]);
    }
}
