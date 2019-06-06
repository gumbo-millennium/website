@inject('menuHelper', 'App\Services\MenuProvider')

@auth
{{-- Add logout form to header --}}
<form class="d-none" id="navbar-logout-form" action="{{ route('logout') }}" method="post">
    @csrf
</form>
@endauth

{{-- Header v2 --}}
<div class="fixed-top">
    @include('main.layout.header.userbar')
    @include('main.layout.header.navbar')
</div>
