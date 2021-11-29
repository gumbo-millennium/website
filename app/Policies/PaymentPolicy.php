<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * Restrict most changes to payments.
 */
class PaymentPolicy
{
    use HandlesAuthorization;

    public const ADMIN_PERMISSION = 'payment-monitor';

    public const PURGE_PERMISSION = 'payment-admin';

    /**
     * Determine whether the user can view any payments.
     *
     * @return bool
     */
    public function viewAny(User $user)
    {
        return $user->can('manage', Payment::class)
            || $user->can('viewAny', Models\Enrollment::class)
            || $user->can('viewAny', Models\Shop\Order::class);
    }

    /**
     * Determine whether the user can view the payment.
     *
     * @return bool
     */
    public function view(User $user, Payment $payment)
    {
        return $user->can('manage', Payment::class)
            || ($payment->payable && $user->can('view', $payment->payable));
    }

    /**
     * Determine whether the user can create payments.
     *
     * @return bool
     */
    public function create(User $user)
    {
        return false;
    }

    /**
     * Determine whether the user can update the payment.
     *
     * @return bool
     */
    public function update(User $user, Payment $payment)
    {
        return false;
    }

    /**
     * Determine whether the user can delete the payment.
     *
     * @return bool
     */
    public function delete(User $user, Payment $payment)
    {
        return false;
    }

    /**
     * Returns if the user is allowed to view payments.
     *
     * @return bool
     */
    public function manage(User $user)
    {
        return $user->hasAnyPermission([
            self::ADMIN_PERMISSION,
            self::PURGE_PERMISSION,
        ]);
    }

    /**
     * Returns if the user is allowed to modify payments.
     *
     * @return bool
     */
    public function admin(User $user)
    {
        return $user->hasPermissionTo(self::ADMIN_PERMISSION);
    }
}
