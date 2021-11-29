<form action="{{ route('logout') }}" method="POST" class="hidden" id="logout-form" aria-label="Logout form">
    @csrf

    <input type="hidden" name="next" value="{{ request()->url() }}">
</form>
