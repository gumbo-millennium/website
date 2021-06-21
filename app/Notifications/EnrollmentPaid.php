<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Contracts\StripeServiceContract;
use App\Helpers\Str;
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
     * @return array
     */
    public function via()
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail()
    {
        // Reload enrollment
        $enrollment = $this->enrollment
            ->refresh()
            ->loadMissing(['user', 'activity', 'activity.role:id,title']);

        // Get some shorthands
        $user = $enrollment->user;
        $activity = $enrollment->activity;

        // Get a link to the receipt page, or download a PDF
        $link = $this->getReceiptLink($this->enrollment);
        $pdf = $link ? null : $this->getInvoicePdf($this->enrollment);

        // Send mail
        $mail = (new MailMessage())
            ->subject("Betaalbevestiging voor {$activity->name} 🎉")
            ->greeting('Bedankt voor je betaling!')
            ->line("Beste {$user->first_name},")
            ->line("Je betaling voor {$activity->name} is in goede orde ontvangen.")
            ->line('Je inschrijving is nu definitief.')
            ->action('Bekijk activiteit', route('activity.show', compact('activity')));

        // Add link to receipt
        if ($link) {
            $mail = $mail
                ->line(<<<TEXT
                Indien je, voor eigen boekhouding of om in te lijsten, graag een betaalbevesting
                 wil hebben, kan je deze [hier bekijken]({$link}).
                TEXT);
        }

        // Add PDF, but only if $link is missing
        if (! $link && $pdf) {
            $mail = $mail
                ->line('In de bijlage vind je een kopie van de factuur.')
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
            ->line("Bedankt voor het gebruiken van de Gumbo Millennium website, ${tail}.");
    }

    /**
     * Returns link to Stripe's receipt page.
     *
     * @throws BindingResolutionException
     */
    public function getReceiptLink(Enrollment $enrollment): ?string
    {
        $stripeService = app(StripeServiceContract::class);
        \assert($stripeService instanceof StripeServiceContract);

        // Get charge
        $charge = $stripeService->getCharge($enrollment);
        if (! $charge) {
            return null;
        }

        // Get link
        $receiptLink = $charge->receipt_url;

        return ! empty($receiptLink) ? $receiptLink : null;
    }

    /**
     * Returns path to the PDF, or null if missing.
     *
     * @throws BindingResolutionException
     */
    private function getInvoicePdf(Enrollment $enrollment): ?string
    {
        $stripeService = app(StripeServiceContract::class);
        \assert($stripeService instanceof StripeServiceContract);

        // Get invoice
        $invoice = $stripeService->getInvoice($enrollment, StripeServiceContract::OPT_NO_CREATE);
        if (! $invoice) {
            return null;
        }

        // Skip if PDF path is missing
        $pdfUri = $invoice->invoice_pdf;
        if (! $pdfUri) {
            return null;
        }

        // Get temp file
        $target = tempnam(\sys_get_temp_dir(), 'pdf');

        try {
            $client = app(Client::class);
            \assert($client instanceof Client);

            // Make get request
            $response = $client->get($pdfUri, [
                // Write response to file
                RequestOptions::SINK => $target,

                // this API connects quickly, but generates PDFs on the fly, which is slow.
                RequestOptions::CONNECT_TIMEOUT => 15.00,
                RequestOptions::READ_TIMEOUT => 30.00,
                RequestOptions::TIMEOUT => 45.00,
            ]);

            // Return if non-200
            if ($response->getStatusCode() !== 200) {
                return null;
            }

            // Generate filename
            $filename = sprintf('%s.pdf', Str::slug("invoice {$invoice->number}"));

            // Store sink
            return Storage::putFileAs('payment/pdfs', new File($target), $filename);
        } catch (ConnectException $exception) {
            logger()->warning(
                'Failed to connect to Stripe for Invoice PDF',
                compact('exception', 'enrollment', 'invoice'),
            );
        } catch (ClientException $exception) {
            logger()->warning(
                'Failed to get Invoice PDF from Stripe server',
                compact('exception', 'enrollment', 'invoice'),
            );
        }

        // Request failed
        return null;
    }
}
