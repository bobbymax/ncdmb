<?php

namespace App\Services;

use App\Http\Resources\ExpenditureResource;
use App\Repositories\ClaimRepository;
use App\Repositories\ExpenditureRepository;
use App\Repositories\FundRepository;
use App\Repositories\ProjectMilestoneRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ExpenditureService extends BaseService
{
    protected FundRepository $fundRepository;
    protected ClaimRepository $claimRepository;
    protected ProjectMilestoneRepository $projectMilestoneRepository;
    public function __construct(
        ExpenditureRepository $expenditureRepository,
        ExpenditureResource $expenditureResource,
        FundRepository $fundRepository,
        ClaimRepository $claimRepository,
        ProjectMilestoneRepository $projectMilestoneRepository
    ) {
        parent::__construct($expenditureRepository, $expenditureResource);
        $this->fundRepository = $fundRepository;
        $this->claimRepository = $claimRepository;
        $this->projectMilestoneRepository = $projectMilestoneRepository;
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

    public function index()
    {
        return $this->repository->instanceOfModel()
                ->where('department_id', Auth::user()->department_id)
                ->where('status', 'reversed')
                ->latest()->get();
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
                        'stage' => 'raise-payment'
                    ]);
                }
            }

            return $expenditure;
        });
    }
}
