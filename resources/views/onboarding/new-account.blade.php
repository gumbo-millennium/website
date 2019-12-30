@extends('layout.main')

@section('title', "Welkom bij Gumbo Millennium")

@section('content')
<div class="header header--activity">
    <div class="container header__container">
        <h1 class="header__title">Welkom bij Gumbo Millennium</h1>
        <p class="header__subtitle">Je account is aangemaakt</p>
    </div>
</div>

<div class="content-block">
    <div class="container content-block__container">
        <p>
            Bedankt voor het aanmaken van een account bij Gumbo Millennium.<br />
            Je bent nu ingelogd.
        </p>
        <p>
            <strong>Let op</strong>: Om je aan te melden voor activiteiten en om berichten te plaatsen, moet je
            nog wel even je e-mail adres bevestigen. Je hebt hierover een mailtje ontvangen.
        </p>
        <p class="mt-4">
            Ben je al lid van Gumbo? Kan wordt je lidstatus automatisch toegewezen, maar dit kan even duren.<br />
            Heb je accuut toegang nodig tot alleen-leden systemen? Neem dan contact op met het bestuur.
        </p>
    </div>
</div>

<div class="bg-brand-600">
    <div class="container my-16">
        <a href="?continue=1" class="btn text-2xl mx-auto">Doorgaan</a>
    </div>
</div>
@endsection
