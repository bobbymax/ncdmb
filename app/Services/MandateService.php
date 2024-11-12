<?php

namespace App\Services;

use App\Http\Resources\MandateResource;
use App\Repositories\FlightItineraryRepository;
use App\Repositories\MandateRepository;
use Illuminate\Support\Facades\DB;

class MandateService extends BaseService
{
    protected FlightItineraryRepository $flightItineraryRepository;
    public function __construct(
        MandateRepository $mandateRepository,
        MandateResource $mandateResource,
        FlightItineraryRepository $flightItineraryRepository
    ) {
        parent::__construct($mandateRepository, $mandateResource);
        $this->flightItineraryRepository = $flightItineraryRepository;
    }

    public function rules($action = "store"): array
    {
        return [
            'fund_id' => 'required|integer|exists:funds,id',
            'vendor_id' => 'required|integer|exists:vendors,id',
            'project_milestone_id' => 'required|integer|exists:project_milestones,id',
            'expenditure_id' => 'sometimes|integer|min:0|exists:expenditures,id',
            'no_of_itineraries' => 'required|integer|min:1',
            'total_payable_amount' => 'required|numeric|min:1',
            'instruction' => 'sometimes|nullable|string|min:3',
            'description' => 'sometimes|nullable|string|min:3',
            'budget_year' => 'required|integer|digits:4',
            'status' => 'sometimes|nullable|string|in:pending,raised,paid,reversed',
            'itineraries' => 'required|array',
        ];
    }

    public function store(array $data)
    {
        return DB::transaction(function () use ($data) {
            $mandate = parent::store($data);

            if ($mandate) {
                foreach($data["itineraries"] as $value) {
                    $itinerary = $this->flightItineraryRepository->find($value['id']);

                    if ($itinerary) {
                        $itinerary->update(['mandate_id' => $mandate->id]);
                    }
                }
            }
        });
    }
}
