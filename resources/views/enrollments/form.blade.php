@extends('layout.main')

@section('title', "Inschrijven voor {$activity->name}")

<?php
$dataRecipients = collect([
    'de organisatie',
    'het bestuur',
    'de digitale commissie'
])->join(', ', ' en ');
?>

@section('content')
<div class="bg-gray-50">
    @component('components.enroll.header', ['activity' => $activity, 'enrollment' => $enrollment])
    <div class="pt-8 leading-relaxed text-lg flex flex-col gap-y-4">
        <p>
            Om verder te gaan moet je een paar vragen beantwoorden. Deze vragen staan hieronder.
        </p>
        <p class="text-sm">
            Je antwoorden worden versleuteld opgeslagen en behandeld conform het <a href="/privacy-policy" target="_blank">privacybeleid</a>.
            De ingevulde gegevens kunnen aan derden worden verstrekt waar nodig (zoals voedselwensen in geval van een etenje),
            maar dit zal altijd minimale data zijn.
        </p>
        <p class="text-sm">
            De data is exlusief toegankelijk voor {{ $dataRecipients }}.
        </p>
    </div>
    @endcomponent

    @if ($errors->isNotEmpty())
    <div class="enroll-column mt-8">
        <div class="notice notice--warning my-0">
            <p>
                @lang('There was a problem submitting the form.')
                @lang("Please re-check all the fields below for errors.")
            </p>
            </ol>
        </div>
    </div>
    @elseif ($submitted)
    <div class="enroll-column mt-8">
        <div class="notice notice--large notice--info my-0">
            <h3 class="notice__title">
                @lang('Welcome back')
            </h3>
            <p>
                @lang("You've already submitted this form, but you can revise information to your liking.")
                @lang("Please check with the organisation after updating, since they might've taken out some information already.")
            </p>
            </ol>
        </div>
    </div>
    @endif

    <div class="grid grid-cols-1 gap-8 enroll-column pb-8">

        <div class="enroll-card prose">
            {!! form($form, ['class' => 'form']) !!}
        </div>

        <form action="{{ route('enroll.cancel', [$activity]) }}" method="POST">
            @csrf

            <button type="submit" class="btn btn--small text-center">
                @lang('Unenroll and return to activity')
            </button>
        </form>
    </div>
</div>
@endsection
