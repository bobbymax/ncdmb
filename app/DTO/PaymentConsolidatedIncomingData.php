<?php

namespace App\DTO;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

readonly class PaymentConsolidatedIncomingData
{
    public function __construct(
        public int $account_code_id,
        public int $budget_year,
        public string $period,
        public int $ledger_id,
        public array $paymentIds,
        public string $transaction_type_id,
        public int $entity_id
    ) {}

    /**
     * @throws ValidationException
     */
    public static function from(array $data): self
    {
        $validator = Validator::make($data, [
            'account_code_id' => 'required|integer|exists:chart_of_accounts,id',
            'ledger_id' => 'required|integer|exists:ledgers,id',
            'entity_id' => 'required|integer',
            'period' => 'required|string',
            'budget_year' => 'required',
            'transaction_type_id' => 'required|string|in:debit,credit',
            'paymentIds' => 'required|array',
            'paymentIds.*' => 'integer|exists:expenditures,id',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $validated = $validator->validated();

        return new self(
            $validated['account_code_id'],
            $validated['budget_year'],
            $validated['period'],
            $validated['ledger_id'],
            $validated['paymentIds'],
            $validated['transaction_type_id'],
            $validated['entity_id'],
        );
    }
}
