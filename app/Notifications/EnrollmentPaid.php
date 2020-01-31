<?php

namespace App\Notifications;

use App\Contracts\StripeServiceContract;
use App\Models\Enrollment;
use GuzzleHttp\Client;
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

        if ($pdf) {
            $mail = $mail
                ->line('In de bijlage vind je de betaalde factuur.')
                ->attachData(Storage::get($pdf), basename($pdf), ['mime' => 'application/pdf']);
        }

        $tail = 'hopelijk tot de volgende!';
        if ($activity->role->title) {
            $tail = "De {$activity->role->title} is je erg dankbaar :)";
        }

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


        // Get PDF
        /** @var Client $client */
        $client = app(Client::class);
        $target = tempnam(\sys_get_temp_dir(), 'pdf');
        $response = $client->get($pdfUri, [
            'http_error' => false,
            'sink' => $target
        ]);

        // Fail if non-200
        if ($response->getStatusCode() !== 200) {
            return null;
        }

        // Store file
        $filename = sprintf('%s.pdf', Str::slug("invoice {$invoice->number}"));
        return Storage::putFileAs('payment/pdfs', new File($target), $filename);
    }
}
