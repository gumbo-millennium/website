<div class="card">
    <form method="POST" action="{{ route('login') }}" class="login__form">
        @csrf
        <input type="hidden" name="password" value="Gumbo" />

        <p class="mb-4">Testing mode enabled. Pick a user to log in as.</p>

        {{-- Login user --}}
        <div class="mb-4 login__field">
            {{-- Label --}}
            <label for="user" class="login__field-label block text-sm mb-2">User</label>

            {{-- Field --}}
            <select name="email" id="user" class="login__field-input form-select block">
                @foreach ($testUsers as $user)
                <option value="{{ $user->email }}">{{ $user->name }}</option>
                @endforeach
            </select>
        </div>

        {{-- Submit button --}}
        <button class="login__submit block btn btn-brand mb-4" type="submit">Inloggen</button>
    </form>
</div>
