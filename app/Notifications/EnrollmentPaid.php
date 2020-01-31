<?php

namespace App\Notifications;

use App\Contracts\StripeServiceContract;
use App\Models\Enrollment;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\RequestOptions;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Http\File;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class EnrollmentPaid extends Notification implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    protected Enrollment $enrollment;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Enrollment $enrollment)
    {
        $this->enrollment = $enrollment->withoutRelations();
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        // Reload enrollment
        $enrollment = $this->enrollment
            ->refresh()
            ->loadMissing(['user', 'activity', 'activity.role:id,title']);

        // Get some shorthands
        $user = $enrollment->user;
        $activity = $enrollment->activity;

        // Try to download the PDF
        $pdf = $this->getInvoicePdf($this->enrollment);

        // Send mail
        $mail = (new MailMessage())
            ->greeting('Bedankt voor je betaling!')
            ->line(<<<TEXT
            Je betaling voor {$activity->name} is in goede orde ontvangen.
            Je inschrijving is nu definitief.
            TEXT)
            ->action('Bekijk activiteit', route('activity.show', compact('activity')));

        // Add PDF if present
        if ($pdf) {
            $mail = $mail
                ->line('In de bijlage vind je de betaalde factuur.')
                ->attachData(Storage::get($pdf), basename($pdf), ['mime' => 'application/pdf']);
        }

        // Finally get some closure
        $tail = 'hopelijk tot de volgende!';

        // Add thank you from the group if present
        $group = optional($activity->role)->title;
        if ($group) {
            $tail = "De {$group} is je erg dankbaar :)";
        }

        // Send the mail with the final goodbye
        return $mail
            ->line("Bedankt voor het gebruiken van de Gumbo Millennium website, $tail.");
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }


    /**
     * Returns path to the PDF, or null if missing
     * @param Enrollment $enrollment
     * @return null|string
     * @throws BindingResolutionException
     */
    private function getInvoicePdf(Enrollment $enrollment): ?string
    {
        /** @var StripeServiceContract $stripeService */
        $stripeService = app(StripeServiceContract::class);

        // Get invoice
        $invoice = $stripeService->getInvoice($enrollment);
        if (!$invoice) {
            return null;
        }

        // Skip if PDF path is missing
        $pdfUri = $invoice->invoice_pdf;
        if (!$pdfUri) {
            return null;
        }

        // Get temp file
        $target = tempnam(\sys_get_temp_dir(), 'pdf');

        try {
            /** @var Client $client */
            $client = app(Client::class);

            // Make get request
            $response = $client->get($pdfUri, [
                // Write response to file
                RequestOptions::SINK => $target,

                // this API connects quickly, but generates PDFs on the fly, which is slow.
                RequestOptions::CONNECT_TIMEOUT => 15.00,
                RequestOptions::READ_TIMEOUT => 30.00,
                RequestOptions::TIMEOUT => 45.00
            ]);

            // Generate filename
            $filename = sprintf('%s.pdf', Str::slug("invoice {$invoice->number}"));

            // Store sink
            return Storage::putFileAs('payment/pdfs', new File($target), $filename);
        } catch (ConnectException $exception) {
            logger()->warning(
                'Failed to connect to Stripe for Invoice PDF',
                compact('exception', 'enrollment', 'invoice')
            );
        } catch (ClientException $exception) {
            logger()->warning(
                'Failed to get Invoice PDF from Stripe server',
                compact('exception', 'enrollment', 'invoice')
            );
        }

        // Request failed
        return null;
    }
}
