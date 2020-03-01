@extends('layout.main')

@section('content')
<article class="container pt-12">
    {{-- Create two-section grid --}}
    <div class="flex row flex-col lg:flex-row-reverse">
        {{-- Metadata --}}
        <div class="col w-full lg:w-5/12 lg:flex-none">
            @yield('two-col.right')
        </div>

        {{-- Main content --}}
        <div class="col w-full lg:w-7/12 lg:flex-none">
            @yield('two-col.left')
        </div>
    </div>

    {{-- After the grid --}}
    @yield('two-col.after')
</article>
@endsection
