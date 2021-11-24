@extends('layout.main')

@section('title', "Inschrijven voor {$activity->name}")

@section('content')
<div class="container container--sm py-8">
    @component('events.enroll-header', [
        'showCancel' => true,
    ])

    {!! form($form, ['class' => 'form']) !!}
</div>
@endsection
