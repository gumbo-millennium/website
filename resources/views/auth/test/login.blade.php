<div class="card border-gray-200 border">
    <form method="POST" action="{{ route('login') }}" class="login__form card__body">
        @csrf
        <input type="hidden" name="password" value="Gumbo" />

        <h2 class="font-base text-2xl">Test mode <strong>enabled</strong></h2>
        <p class="mb-8 text-lg">Pick a test user from the drop down below to quickly log in.</p>

        {{-- Login user --}}
        <div class="flex flex-col md:flex-row md:items-center">
            <div class="flex-grow mb-4 md:mb-0">
                {{-- Field --}}
                <select name="email" id="user" class="form-select w-full text-gray-900">
                    @foreach ($testUsers as $user)
                    <option value="{{ $user->email }}">{{ $user->name }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Submit button --}}
            <button class="my-0 py-2 btn btn-brand md:ml-4" type="submit">Inloggen</button>
        </div>
    </form>
</div>
