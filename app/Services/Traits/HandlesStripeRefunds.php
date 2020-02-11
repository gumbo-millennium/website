<?php

declare(strict_types=1);

namespace App\Services\Traits;

use App\Contracts\StripeServiceContract;
use App\Models\Enrollment;
use InvalidArgumentException;
use Stripe\Charge;
use Stripe\CreditNote;
use Stripe\Exception\ApiErrorException;
use Stripe\Invoice;
use Stripe\Refund;
use UnderflowException;

trait HandlesStripeRefunds
{
    /**
     * @var array<string>
     */
    private static array $validRefundReasons = [
        StripeServiceContract::REFUND_REQUESTED_BY_CUSTOMER,
        StripeServiceContract::REFUND_DUPLICATE,
        StripeServiceContract::REFUND_FRAUDULENT,
    ];

    /**
     * @var array<string>
     */
    private static array $validCreditNoteReasons = [
        StripeServiceContract::CREDIT_DUPLICATE,
        StripeServiceContract::CREDIT_FRAUDULENT,
        StripeServiceContract::CREDIT_ORDER_CHANGE,
        StripeServiceContract::CREDIT_PRODUCT_UNSATISFACTORY,
    ];

    /**
     * Maps refund reasons to credit note reasons
     * @var array<string>
     */
    private static array $refundCreditReasonMap = [
        StripeServiceContract::REFUND_REQUESTED_BY_CUSTOMER => StripeServiceContract::CREDIT_ORDER_CHANGE
    ];

    /**
     * Creates a refund for the given enrollment, which will issue a refund for the
     * charge, and then add that refund as a credit note on the invoice.
     * @param Enrollment $enrollment
     * @param string $reason
     * @param null|int $amount
     * @return Refund
     */
    public function createRefund(Enrollment $enrollment, string $reason, ?int $amount): Refund
    {
        // Get charge
        $invoice = $this->getInvoice($enrollment, StripeServiceContract::OPT_NO_CREATE);
        $charge = $this->getCharge($enrollment);

        if (!$invoice) {
            return new UnderflowException('No invoice has been created for the given enrollment.');
        }

        if (!$charge) {
            return new UnderflowException('No charge has been made for the given enrollment.');
        }

        // Amount
        $amount = $amount > 0 ? $amount : null;

        // Create the refund
        $refund = $this->createStripeRefund($charge, $enrollment, $invoice, $reason, $amount);

        // Create credit note for the refund
        $this->createStripeCreditNote($refund, $enrollment, $invoice, $reason);

        // Return the refund
        return $refund;
    }

    /**
     * Creates the refund for the given charge, and returns it
     * @param Charge $charge
     * @param Enrollment $enrollment
     * @param Invoice $invoice
     * @param string $reason
     * @param null|int $amount
     * @return Refund
     * @throws UnderflowException
     * @throws InvalidArgumentException
     */
    private function createStripeRefund(
        Charge $charge,
        Enrollment $enrollment,
        Invoice $invoice,
        string $reason,
        ?int $amount
    ) {
        // Calculate remaining funds
        $remainingFunds = $charge->amount - $charge->amount_refunded;

        // Can't refund if the enrollment is already fully refunded
        if ($charge->refunded || $remainingFunds <= 0) {
            throw new UnderflowException('Cannot refund from an already refunded charge');
        }

        // Can't refund more than available
        if ($amount > $remainingFunds) {
            throw new InvalidArgumentException('Cannot refund more than remaining on this charge');
        }

        // Build data
        $data = [
            'charge' => $charge->id,
            'amount' => $amount,
            'metadata' => [
                'enrollment-id' => $enrollment->id,
                'user-id' => $enrollment->user->id,
                'invoice-id' =>  $invoice->number
            ]
        ];

        // Add reason
        if (\in_array($reason, self::$validRefundReasons)) {
            $data['reason'] = $reason;
        }

        // Purge empty values
        $data = \array_filter($data, static fn ($row) => !empty($row));

        try {
            // Create the refund
            return Refund::create($data);
        } catch (ApiErrorException $exception) {
            // Bubble all
            $this->handleError($exception);
        }
    }

    /**
     * Creates the credit note for this invoice, created from the refund provided
     * @param Refund $refund
     * @param Enrollment $enrollment
     * @param Invoice $invoice
     * @param string $reason
     * @return CreditNote
     */
    private function createStripeCreditNote(
        Refund $refund,
        Enrollment $enrollment,
        Invoice $invoice,
        string $reason
    ): CreditNote {
        // Translation for refunds
        if (isset(self::$refundCreditReasonMap[$reason])) {
            $reason = self::$refundCreditReasonMap[$reason];
        }

        // Data
        $data = [
            'invoice' => $invoice->id,
            'refund' => $refund->id,
            'metadata' => [
                'enrollment-id' => $enrollment->id,
                'user-id' => $enrollment->user->id,
                'invoice-id' =>  $invoice->number
            ]
        ];

        // Add reason
        if (in_array($reason, self::$validCreditNoteReasons)) {
            $data['reason'] = $reason;
        }

        try {
            // Create the credit note
            return CreditNote::create($data);
        } catch (ApiErrorException $exception) {
            // Bubble all
            $this->handleError($exception);
        }
    }
}
