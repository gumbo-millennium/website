<div class="card border-gray-200 border">
  <div class="card__body">
    <form method="POST" action="{{ route('login') }}" class="grid grid-cols-1 gap-2">
        @csrf
        <input type="hidden" name="password" value="Gumbo" />

        <p>
          <strong>Test mode is active!</strong> Would you like to login as a dummy user?
        </p>

        <x-input name="email" :label="__('Account')">
          <x-slot name="input">
            <select name="email" id="user" class="block w-full sm:text-sm rounded-md shadow-sm focus:ring-brand-500 focus:border-brand-500 border-gray-300">
              @foreach ($users as $user)
              <option value="{{ $user->email }}">{{ $user->name }}</option>
              @endforeach
            </select>
          </x-slot>
        </x-input>

        <x-button type="submit" style="primary">
          @lang('Login')
        </x-button>
    </form>
  </div>
</div>
