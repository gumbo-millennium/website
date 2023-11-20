<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Account;

use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Tests\TestCase;

class PasswordControllerTest extends TestCase
{
    use WithFaker;

    public static function provideBadNewPasswords(): array
    {
        return [
            'too short' => ['hello'],
            'too long' => [str_repeat('a', 256)],
            'compromised' => ['password1234'],
        ];
    }

    public static function provideBadOldPasswords(): array
    {
        return [
            'empty' => [''],
            'too long' => [str_repeat('a', 256)],
            'wrong' => ['not-my-password'],
        ];
    }

    /**
     * Test form view.
     */
    public function test_render_view(): void
    {
        $this->get(route('account.password.edit'))
            ->assertRedirect(route('login'));

        $this->actingAs($this->getTemporaryUser());

        $this->get(route('account.password.edit'))
            ->assertOk();
    }

    /**
     * Test a valid password change.
     */
    public function test_change_password(): void
    {
        $this->actingAs($user = $this->getTemporaryUser());

        $user->forceFill([
            'password' => Hash::make('test-password'),
        ])->save();

        $newPassword = "my-new-password-{$this->faker->uuid()}";

        $this->post(route('account.password.update'), [
            'current_password' => 'test-password',
            'new_password' => $newPassword,
        ])
            ->assertSessionDoesntHaveErrors()
            ->assertRedirect(route('account.password.edit'));

        $this->assertTrue(Hash::check($newPassword, $user->fresh()->password));
    }

    /**
     * Check that new passwords meet some requirements.
     * @dataProvider provideBadNewPasswords
     */
    public function test_password_requirements(string $newPassword): void
    {
        $this->actingAs($user = $this->getTemporaryUser());

        $oldPassword = 'test-password' ;

        $user->forceFill([
            'password' => Hash::make($oldPassword),
        ])->save();

        Session::setPreviousUrl(route('account.password.edit'));

        $this->post(route('account.password.update'), [
            'current_password' => $oldPassword,
            'new_password' => $newPassword,
        ])
            ->assertSessionHasErrors('new_password')
            ->assertRedirect(route('account.password.edit'));

        $this->assertTrue(Hash::check($oldPassword, $user->fresh()->password));
    }

    /**
     * Ensure old passwords must match, be present and don't provide a way to spam the server.
     * @dataProvider provideBadOldPasswords
     */
    public function test_old_password_verification(string $oldPassword): void
    {
        $this->actingAs($user = $this->getTemporaryUser());

        $actualOldPassword = 'my-password';

        $user->forceFill([
            'password' => Hash::make($actualOldPassword),
        ])->save();

        Session::setPreviousUrl(route('account.password.edit'));

        $this->post(route('account.password.update'), [
            'current_password' => $oldPassword,
            'new_password' => $this->faker->sentence(3) . $this->faker->uuid(),
        ])
            ->assertSessionHasErrors('current_password')
            ->assertRedirect(route('account.password.edit'));

        $this->assertTrue(Hash::check($actualOldPassword, $user->fresh()->password));
    }

    /**
     * Ensure a user without a password cannot change the password using an empty
     * string.
     */
    public function test_empty_password_handling(): void
    {
        $this->actingAs($user = $this->getTemporaryUser());

        $user->forceFill([
            'password' => '',
        ])->save();

        Session::setPreviousUrl(route('account.password.edit'));

        $this->post(route('account.password.update'), [
            'current_password' => '',
            'new_password' => $this->faker->sentence(3) . $this->faker->uuid(),
        ])
            ->assertSessionHasErrors('current_password')
            ->assertRedirect(route('account.password.edit'));

        $this->assertEmpty($user->fresh()->password);
    }
}
