@extends('layout.main')

@section('title', "Inschrijven voor {$activity->name}")

@section('content')
<div class="bg-gray-50">
    @component('components.enroll.header', ['activity' => $activity, 'enrollment' => $enrollment])
    <div class="pt-8 leading-relaxed text-lg flex flex-col gap-y-4">
        <p>
            Je inschrijving voor {{ $activity->name }} is bijna af. Je hoeft alleen nog te betalen.<br />

            Dit kan online <strong>exclusief</strong> via iDEAL. Wil je liever betalen via overboeking
            of via een andere afspraak met het bestuur? Neem dan contact op met het bestuur.
        </p>
    </div>
    @endcomponent

    <div class="grid grid-cols-1 gap-8 enroll-column pb-8">

        @include('enrollments.partials.enrollment-data')

        <hr class="my-8 bg-gray-400" />

        <form formaction="{{ route('enroll.payStore', [$activity]) }}" method="POST">
            @csrf

            <div class="flex flex-col gap-4 md:flex-row-reverse" method="POST">
                <button type="submit" class="w-full btn btn--small m-0 btn--brand text-center">
                    @lang('Continue')
                </button>

                <button formnovalidate formaction="{{ route('enroll.cancel', [$activity]) }}" type="submit" class="w-full btn btn--small m-0 text-center">
                    @lang('Unenroll')
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
