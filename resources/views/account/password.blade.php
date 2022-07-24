<x-account-page :accountTitle="__('Change Password')">

  <p class="leading-loose mb-2">
    @lang('Enter your old password, followed by a new password below. The new password must be at least 8 characters long.')
  </p>
  <form action="{{ route('account.password.update') }}" method="POST">
    @csrf

    <div class="mb-4">
        <x-input :label="__('Current password')" type="password" name="current_password" autocomplete="current-password" required />
    </div>

    <hr class="bg-gray-200 my-4" />

    <div class="mb-4">
      <x-input
        :label="__('New password')"
        :help="__('You cannot use a password that\'s been registered in a data breach. We use K-anonimity to safely check your password against known breaches on your email address')"
        type="password"
        name="new_password"
        autocomplete="new-password"
        min="8"
        required  />
    </div>

    <div class="mb-4">
      <x-checkbox
        type="checkbox"
        name="logout"
        :label="__('Log me out on all other devices')"
        :help="__('If you want to secure your account after changing your password, check this box to terminate all other sessions.')"
      />
    </div>

    <hr class="bg-gray-200 my-4" />

    <div class="mb-4">

    </div>

    <x-button style="primary" type="submit">
      @lang('Change Password')
    </x-button>
  </form>
</x-account-page>
