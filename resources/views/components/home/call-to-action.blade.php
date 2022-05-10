@props([
  'activity' => null,
])
@if ($activity)
  <div class="bg-brand-700">
    <div class="max-w-3xl mx-auto text-center py-16 px-4 sm:py-20 sm:px-6 lg:px-8">
      <h2 class="text-3xl font-title font-extrabold text-white sm:text-4xl">
        Begin je studie goed,<br />
        Schrijf je in voor onze introductieweek!
      </h2>
      <p class="mt-4 text-lg leading-6 text-brand-200 max-w-2xl mx-auto">
        Van {{ $activity->start_date->isoFormat('dd MMMM') }} t/m {{ $activity->end_date->isoFormat('dd MMMM') }} is onze
        introductieweek.
        Dé manier om jouw tijd bij Gumbo Millennium geweldig te beginnen!
      </p>
      <a href="{{ route('join.form-intro') }}"
        class="mt-8 w-full inline-flex items-center justify-center px-5 py-3 border border-transparent text-base font-medium rounded-md text-brand-600 bg-white hover:bg-brand-50 sm:w-auto">
        Schrijf je in
      </a>
    </div>
  </div>
@else
  <div class="bg-brand-700">
    <div class="max-w-3xl mx-auto text-center py-16 px-4 sm:py-20 sm:px-6 lg:px-8">
      <h2 class="text-3xl font-extrabold text-white sm:text-4xl">
        Maak het beste van je studententijd,<br />
        Word lid van Gumbo Millennium!
      </h2>
      <p class="mt-4 text-lg leading-6 text-brand-200 max-w-2xl mx-auto">
        Maak je studententijd extra gezellig en doe mee met de leukste activiteiten,
        door je in te schrijven bij Gumbo Millennium.
      </p>
      <a href="{{ route('join.form') }}"
        class="mt-8 w-full inline-flex items-center justify-center px-5 py-3 border border-transparent text-base font-bold rounded-md text-brand-600 bg-white hover:bg-brand-50 sm:w-auto">
        Schrijf je in
      </a>
    </div>
  </div>
@endif
