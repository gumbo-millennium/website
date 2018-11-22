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
 * Mail sent to the board about a request to join
 *
 * @author Roelof Roos <github@roelof.io>
 * @license MPL-2.0
 */
class JoinBoardMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * The join request to handle
     *
     * @var Collection
     */
    protected $data;

    /**
     * Sends an e-mail to the board about someone having signed up
     *
     * @param Collection $data
     * @param array $userRecepient
     * @param array $boardRecipient
     */
    public function __construct(Collection $data, array $userRecepient, array $boardRecipient)
    {
        // Set the data
        $this->data = $data;

        // Set to and reply-to headers
        $this->replyTo($userRecepient['email'], $userRecepient['name']);
        $this->to($boardRecipient['email'], $boardRecipient['name']);

        // Set subject
        $this->subject("[site] Nieuw lidmaatschap {$userRecepient['name']}");

        // Log entry
        logger()->info('Sending join e-mail copy to [recipient]', [
            'data' => $data,
            'to' => $userRecepient,
            'reply-to' => $boardRecipient,
        ]);
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('mail.join.new-board')->with([
            'joinData' => $this->data
        ]);
    }
}
