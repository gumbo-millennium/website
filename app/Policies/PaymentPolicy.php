<?php

declare(strict_types=1);

namespace App\Policies;

use App\Model\Payment;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * Restrict most changes to payments
 */
class PaymentPolicy
{
    // phpcs:disable SlevomatCodingStandard.Functions.UnusedParameter
    use HandlesAuthorization;

    public const ADMIN_PERMISSION = 'payment-monitor';

    public const PURGE_PERMISSION = 'payment-admin';

    /**
     * Determine whether the user can view any payments.
     * @param  User  $user
     * @return bool
     */
    public function viewAny(User $user)
    {
        return $user->can('manage', Payment::class);
    }

    /**
     * Determine whether the user can view the payment.
     * @param  User  $user
     * @param  Payment  $payment
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function view(User $user, Payment $payment)
    {
        return $user->can('manage', Payment::class);
    }

    /**
     * Determine whether the user can create payments.
     * @param  User  $user
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function create(User $user)
    {
        return false;
    }

    /**
     * Determine whether the user can update the payment.
     * @param  User  $user
     * @param  Payment  $payment
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function update(User $user, Payment $payment)
    {
        return false;
    }

    /**
     * Determine whether the user can delete the payment.
     * @param  User  $user
     * @param  Payment  $payment
     * @return bool
     */
    public function delete(User $user, Payment $payment)
    {
        return $user->can('admin', Payment::class) && $payment->created_at < today()->subYear(7);
    }

    /**
     * Returns if the user is allowed to view payments
     * @param User $user
     * @return bool
     */
    public function manage(User $user)
    {
        return $user->hasAnyPermission([
            self::ADMIN_PERMISSION,
            self::PURGE_PERMISSION
        ]);
    }

    /**
     * Returns if the user is allowed to modify payments
     * @param User $user
     * @return bool
     */
    public function admin(User $user)
    {
        return $user->hasPermissionTo(self::ADMIN_PERMISSION);
    }
}
