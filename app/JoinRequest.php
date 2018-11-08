<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

/**
 * A request to join the wonderful community. Is approved by the board
 * via the admin panel, which will grant the user access to the
 * file systems.
 *
 * @author Roelof Roos <github@roelof.io>
 * @license MPL-2.0
 */
class JoinRequest extends Model
{
    /**
     * Returns if users can join
     *
     * @param Request $request
     * @return bool
     */
    public function canJoin(Request $request) : bool
    {
        $user = $request->user();
        return !($user && $user->hasRole('member'));
    }
    /**
     * Creates a new join request using the data provided.
     * Does NOT validate request
     *
     * @param User $user
     * @param array $data
     * @return self|null
     */
    public static function makeAndSend(User $user, array $data) : ?self
    {
        if (!$user->exists) {
            return null;
        }

        $joinRequest = self::create([
            'user_id' => $user->id,
            'street' => $user->street,
            'number' => $user->number,
            'zipcode' => $user->zipcode,
            'city' => $user->city,
            'country' => $user->country,
            'phone' => $user->phone,
            'date-of-birth' => $user->dateOfBirth,
            'accept-policy' => $user->acceptPolicy,
            'accept-newsletter' => $user->acceptNewsletter,
        ]);

        return $user->exists ? $user : null;
    }

    /**
     * Only returns requests that are still pending
     *
     * @param Builder $builer
     * @return Builder
     */
    public function scopePending(Builder $builer)
    {
        return $this->where('status', 'pending');
    }

    /**
     * Only returns requests that are accepted by the board
     *
     * @param Builder $builer
     * @return Builder
     */
    public function scopeAccepted(Builder $builer)
    {
        return $this->where('status', 'accepted');
    }

    /**
     * Only returns requests that are declined by the board
     *
     * @param Builder $builer
     * @return void
     */
    public function scopeDeclined(Builder $builer)
    {
        return $this->where('status', 'declined');
    }

    /**
     * Only returns requests that are still pending
     *
     * @param Builder $builer
     * @param User $user
     * @return Builder
     */
    public function scopeForUser(Builder $builer, User $user)
    {
        return $this->where('user_id', $user->id);
    }
}
