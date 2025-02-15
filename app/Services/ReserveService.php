<?php

namespace App\Services;

use App\Repositories\ExpenditureRepository;
use App\Repositories\FundRepository;
use App\Repositories\ReserveRepository;
use App\Repositories\UploadRepository;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class ReserveService extends BaseService
{
    protected UploadRepository $uploadRepository;
    protected FundRepository $fundRepository;
    protected ExpenditureRepository $expenditureRepository;
    public function __construct(
        ReserveRepository $reserveRepository,
        UploadRepository $uploadRepository,
        FundRepository $fundRepository,
        ExpenditureRepository $expenditureRepository
    ) {
        parent::__construct($reserveRepository);
        $this->uploadRepository = $uploadRepository;
        $this->fundRepository = $fundRepository;
        $this->expenditureRepository = $expenditureRepository;
    }

    public function rules($action = "store"): array
    {
        $rules = [
            'user_id' => 'required|integer|exists:users,id',
            'department_id' => 'required|integer|exists:departments,id',
            'staff_id' => 'sometimes|min:0|integer',
            'total_reserved_amount' => 'required|numeric|min:0',
            'approval_reversal_memo' => 'sometimes|nullable|mimes:pdf,jpeg,png,jpg|max:2048',
            'date_reserved_approval_or_denial' => 'sometimes|nullable|date',
            'fulfilled' => 'sometimes|boolean',
            'reservable_id' => 'required|integer|min:1',
            'status' => 'required|string|in:pending,secured,released,reversed,rejected',
            'approval_memo' => 'sometimes|nullable|mimes:pdf|max:4096',
        ];

        if ($action == "update") {
            $rules['destination_fund_id'] = 'required|integer|min:1|exists:funds,id';
            $rules['expenditure_id'] = 'required|integer|min:1|exists:expenditures,id';
        }

        return $rules;
    }

    public function update(int $id, array $data, $parsed = true)
    {
        return DB::transaction(function () use ($id, $data) {
            $reserve = parent::update($id, $data);
            if (!$reserve) {
                return null;
            }

            // Handle file uploads
            $this->handleFileUploads($data, $reserve);

            $fund = $this->fundRepository->find($reserve->fund_id);
            if (!$fund) {
                return $reserve; // Return early if no fund found
            }

            switch ($reserve->status) {
                case "secured":
                    $this->handleSecuredStatus($reserve, $fund);
                    break;
                case "released":
                    $this->handleReleasedStatus($reserve, $fund);
                    break;
                case "reversed":
                    $reserve->update([
                        'destination_fund_id' => 0,
                        'expenditure_id' => 0,
                    ]);
                    break;
                case "rejected":
                    $this->handleRejectedStatus($reserve, $fund);
                    break;
            }

            return $reserve;
        });
    }

    /**
     * @throws \Exception
     */
    private function handleFileUploads(array $data, $reserve): void
    {
        if (
            isset($data['approval_reversal_memo']) &&
            $data['approval_reversal_memo'] instanceof UploadedFile &&
            !$reserve->approval_reversal_memo_path
        ) {
            $path = $this->handleFileUpload($data['approval_reversal_memo'], 'reserves/reversals', 'reversal-memo-');
            $reserve->update(['approval_reversal_memo_path' => $path]);
        }

        if (isset($data['approval_memo']) && $data['approval_memo'] instanceof UploadedFile) {
            $path = $this->handleFileUpload($data['approval_memo'], 'reverses/approval-memos', 'approval-memo-');
            $reserve->update(['approval_memo_path' => $path]);
        }
    }

    private function handleSecuredStatus($reserve, $fund): void
    {
        if (!$reserve->secured) {
            $this->increaseReservedAmount($fund, $reserve->total_reserved_amount);
            $reserve->update(['secured' => true]);
        }
    }

    /**
     * @throws \Exception
     */
    private function handleReleasedStatus($reserve, $fund): void
    {
        if (!$reserve->fulfilled && $reserve->approval_memo_path !== null) {
            $this->handleFundRelease($fund, $reserve);
            $reserve->update(['fulfilled' => true]);
        }
    }

    private function handleRejectedStatus($reserve, $fund): void
    {
        if (!$reserve->is_rejected && $reserve->approval_reversal_memo_path !== null) {
            $this->handleFundRejected($fund, $reserve);
            $reserve->update(['is_rejected' => true]);
        }
    }

    private function increaseReservedAmount($fund, $amount): void
    {
        $fund->total_reserved_amount += $amount;
        $fund->save();
    }

    /**
     * @throws \Exception
     */
    private function handleFileUpload(UploadedFile $file, string $filePath, string $prefix)
    {
        $uploadData = [
            'name' => $prefix . time() . '.' . $file->getClientOriginalExtension(),
            'mime_type' => $file->getClientMimeType(),
            'extension' => $file->getClientOriginalExtension(),
            'path' => $file->store($filePath, 'public'),
            'size' => $file->getSize(),
        ];

        $this->uploadRepository->create($uploadData);

        return $uploadData['path'];
    }

    private function handleFundRejected($fund, $reserve): void
    {
        $amount = $reserve->total_reserved_amount;
        $fund->total_reserved_amount -= $amount;
        $fund->save();
    }

    /**
     * @throws \Exception
     */
    private function handleFundRelease($fund, $reserve): void
    {
        $destinationFund = $this->fundRepository->find($reserve->destination_fund_id);
        if (!$destinationFund) {
            return;
        }

        $amount = $reserve->total_reserved_amount;

        $this->generateExpenditure($fund, $reserve, $amount);
        $this->handleFundUpdate($fund, $amount);
        $this->handleFundUpdate($destinationFund, $amount, 'credit');
    }

    /**
     * @throws \Exception
     */
    private function generateExpenditure($fund, $reserve, $amount): void
    {
        $exp = [
            'staff_id' => $reserve->beneficiary->id,
            'fund_id' => $fund->id,
            'beneficiary_name' => $reserve->beneficiary->firstname . ' ' . $reserve->beneficiary->surname,
            'payment_description' => 'PYMT for ' . $reserve->description,
            'total_amount_raised' => $amount,
            'type' => 'staff',
            'flag' => 'credit',
            'payment_category' => 'other',
            'stage' => 'posting',
            'status' => 'refunded',
            'budget_year' => config('site.budget_year'),
        ];

        $this->expenditureRepository->create($exp);
    }

    private function handleFundUpdate($fund, $amount, $operation = "debit"): void
    {
        if ($operation === "credit") {
            $fund->total_expected_spent_amount -= $amount;
            $fund->total_actual_spent_amount -= $amount;
            $fund->total_booked_balance += $amount;
            $fund->total_actual_balance += $amount;
        } else {
            $fund->total_reserved_amount -= $amount;
            $fund->total_expected_spent_amount += $amount;
            $fund->total_actual_spent_amount += $amount;
            $fund->total_booked_balance -= $amount;
            $fund->total_actual_balance -= $amount;
        }

        $fund->save();
    }
}
