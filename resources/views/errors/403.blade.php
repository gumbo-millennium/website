@extends('layout.variants.error')

@php
$message = __($exception->getMessage());
if (empty($message)) {
    $message = 'Sorry, jouw account heeft niet de benodigde rechten om deze pagina te zien.';
}
@endphp

@section('error.title', 'Toegang geweigerd')
@section('error.code', '403')
@section('error.message', $message)
