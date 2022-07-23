@if (Auth::check())
<form class="hidden" action="{{ route('logout') }}" method="post" id="logout-form" name="logout-form" role="none"
  aria-hidden="true">
  @csrf
  <input type="hidden" name="next" value="{{ Request::url() }}">
</form>
@endif
