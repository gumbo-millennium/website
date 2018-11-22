<?php

declare(strict_types=1);

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Http\Requests\JoinRequest;
use Illuminate\Support\Collection;

/**
 * Mail sent to a user confirming his request to join
 *
 * @author Roelof Roos <github@roelof.io>
 * @license MPL-2.0
 */
class JoinMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * The join request to handle
     *
     * @var JoinRequest
     */
    protected $data;

    /**
     * Creates an email for the user about their registration
     *
     * @param Collection $data
     * @param array $userRecepient
     * @param array $boardRecipient
     */
    public function __construct(Collection $data, array $userRecepient, array $boardRecipient)
    {
        $this->data = $data;

        // Set to and reply-to headers
        $this->to($userRecepient['email'], $userRecepient['name']);
        $this->replyTo($boardRecipient['email'], $boardRecipient['name']);

        // Subject
        $this->subject('Je aanmelding bij Gumbo Millennium');
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        logger()->info('Sending join e-mail to [recipient]', [
            'data' => $data,
            'to' => $boardRecipient,
            'reply-to' => $userRecepient,
        ]);

        return $this->markdown('mail.join.new')->with([
            'joinData' => $this->data
        ]);
    }
}
