<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

/**
 * User sent to a user about his new account.
 *
 * @author Roelof Roos <github@roelof.io>
 * @license MPL-2.0
 */
class NewAccountMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    /**
     * User that was registered
     *
     * @var User
     */
    protected $user;

    /**
     * Encrypted password of the user
     *
     * @var string
     */
    protected $password;

    /**
     * Creates an email for the user informing them of their new account
     *
     * @param User $user
     * @param string $password
     */
    public function __construct(User $user, string $password)
    {
        $this->user = $user;
        $this->password = encrypt($password);
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('mail.new-acount')->with([
            'user' => $this->user,
            'password' => decrypt($this->password)
        ]);
    }
}
