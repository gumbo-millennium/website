@if (file_exists('/.dockerenv') && app()->environment('local'))
@php
$possibleUsers = App\Models\User::query()
->where('email', 'like', '%@example.com')
->orderBy('email', 'ASC')
->get();
@endphp
<form method="post" class="py-2 px-4 my-2 rounded border border-gray-300" action="{{ route('login') }}" aria-label="{{ __('Login') }}">
    @csrf
    <h4 class="text-lg">Test credentials</h4>
    <p class="text-gray-700">Docker supplied some test users. Use the dropdown to pick an account</p>

    <input type="hidden" name="password" value="Gumbo" />
    <select name="email" class="form-select mt-2 block w-full">
        @foreach ($possibleUsers as $user)
        <option value="{{ $user->email }}">{{ $user->first_name }}</option>
        @endforeach
    </select>
    <button class="btn btn-brand mt-2" type="submit">Login</button>
</form>
@endif
