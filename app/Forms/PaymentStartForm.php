<?php

declare(strict_types=1);

namespace App\Forms;

use App\Services\IdealBankService;
use Illuminate\Validation\Rule;
use Kris\LaravelFormBuilder\Form;

/**
 * Payment start form
 *
 * @package App\Forms
 */
class PaymentStartForm extends Form
{
    protected IdealBankService $bankService;

    public function __construct(IdealBankService $bankService)
    {
        $this->bankService = $bankService;
    }

    /**
     * Builds the form
     */
    public function buildForm()
    {
        $banks = $this->bankService->getAll();

        $this
            ->add('bank', 'select', [
                'label' => 'Bank',
                'rules' => ['required', Rule::in(array_keys($banks))],
                'choices' => $banks,
                'attr' => [
                    'autofocus' => true,
                ],
            ])
            ->add('submit', 'submit', [
                'label' => 'Start betaling',
            ]);
    }
}
