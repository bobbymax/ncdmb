<?php

namespace App\Services;

use App\Repositories\ClaimRepository;
use App\Repositories\ExpenditureRepository;
use App\Repositories\FundRepository;
use App\Repositories\MandateRepository;
use App\Repositories\ProjectMilestoneRepository;
use Illuminate\Support\Facades\DB;

class ExpenditureService extends BaseService
{
    protected FundRepository $fundRepository;
    protected ClaimRepository $claimRepository;
    protected ProjectMilestoneRepository $projectMilestoneRepository;
    protected MandateRepository $mandateRepository;
    public function __construct(
        ExpenditureRepository $expenditureRepository,
        FundRepository $fundRepository,
        ClaimRepository $claimRepository,
        ProjectMilestoneRepository $projectMilestoneRepository,
        MandateRepository $mandateRepository,
    ) {
        parent::__construct($expenditureRepository);
        $this->fundRepository = $fundRepository;
        $this->claimRepository = $claimRepository;
        $this->projectMilestoneRepository = $projectMilestoneRepository;
        $this->mandateRepository = $mandateRepository;
    }

    public function rules($action = "store"): array
    {
        return [
            'fund_id' => 'required|integer|exists:funds,id',
            'batch_id' => 'sometimes|integer|min:0|exists:batches,id',
            'vendor_id' => 'sometimes|integer|min:0|exists:vendors,id',
            'staff_id' => 'sometimes|integer|min:0|exists:users,id',
            'project_milestone_id' => 'sometimes|integer|min:0|exists:project_milestones,id',
            'claim_id' => 'sometimes|integer|min:0|exists:claims,id',
            'mandate_id' => 'sometimes|integer|min:0|exists:mandates,id',
            'beneficiary_name' => 'required|string|max:255',
            'payment_description' => 'required|string|min:5',
            'additional_info' => 'nullable|string|min:5',
            'total_amount_raised' => 'required|numeric|min:1',
            'total_approved_amount' => 'sometimes|numeric|min:1',
            'flag' => 'required|string|in:debit,credit',
            'type' => 'required|string|in:staff-payment,third-party-payment',
            'payment_category' => 'required|string|in:staff-claim,touring-advance,project,mandate,other',
            'stage' => 'required|string|in:raised,batched,dispatched,budget-office,treasury,audit,posting',
            'status' => 'nullable|string|in:pending,cleared,queried,paid,reversed,refunded',
            'budget_year' => 'required|integer|digits:4',
        ];
    }

    public function store(array $data)
    {
        return  DB::transaction(function () use ($data) {
            $expenditure = parent::store($data);

            $fund = $this->fundRepository->find($expenditure->fund_id);
            $fund->total_booked_balance -= $expenditure->total_amount_raised;
            $fund->total_expected_spent_amount += $expenditure->total_amount_raised;
            $fund->save();

            if ($expenditure->type === 'staff-payment' && $expenditure->payment_category !== 'other') {
                $claim = $this->claimRepository->find($expenditure->claim_id);

                $claim->update([
                    'status' => 'raised'
                ]);

            } else if (($expenditure->type === 'third-party-payment')) {
                if ($expenditure->payment_category === 'milestone') {
                    $projectMilestone = $this->projectMilestoneRepository->find($expenditure->project_milestone_id);

                    $projectMilestone->update([
                        'stage' => 'raise-payment',
                        'status' => 'in-progress'
                    ]);
                } else if ($expenditure->payment_category === 'mandate') {

                    if (isset($data['mandate_id']) && (int) $data['mandate_id'] > 0) {
                        $mandate = $this->mandateRepository->find($data['mandate_id']);

                        if ($mandate) {
                            $mandate->update([
                                'expenditure_id' => $expenditure->id,
                                'status' => 'raised'
                            ]);
                        }
                    }
                }
            }

            return $expenditure;
        });
    }

    public function destroy(int $id)
    {
        return DB::transaction(function () use ($id) {

            $expenditure = $this->repository->find($id);

            if ($expenditure) {
                $fund = $this->fundRepository->find($expenditure->fund_id);

                if (!$fund) {
                    return false;
                }

                if ($expenditure->type === 'staff-payment' && $expenditure->payment_category !== 'other') {
                    $claim = $this->claimRepository->find($expenditure->claim_id);

                    $claim->update([
                        'status' => 'registered'
                    ]);

                } else if (($expenditure->type === 'third-party-payment')) {
                    if ($expenditure->payment_category === 'milestone') {
                        $projectMilestone = $this->projectMilestoneRepository->find($expenditure->project_milestone_id);

                        $projectMilestone->update([
                            'stage' => 'payment-mandate',
                            'status' => 'pending'
                        ]);
                    } else if ($expenditure->payment_category === 'mandate') {

                        $mandate = $this->mandateRepository->find($expenditure->mandate_id);

                        if ($mandate) {
                            $mandate->update([
                                'expenditure_id' => 0,
                                'status' => 'pending'
                            ]);
                        }
                    }
                }

                $amount = $expenditure->total_amount_raised;

                $fund->total_expected_spent_amount -= $amount;
                $fund->total_actual_spent_amount -= $amount;
                $fund->total_booked_balance += $amount;
                $fund->total_actual_balance += $amount;
                $fund->save();


                $expenditure->update(['status' => 'reversed']);
            }

            return true;
        });
    }
}
