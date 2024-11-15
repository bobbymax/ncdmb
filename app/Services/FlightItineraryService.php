<?php

namespace App\Services;

use App\Models\FlightItinerary;
use App\Repositories\FlightItineraryRepository;
use App\Repositories\ReserveRepository;
use App\Repositories\UploadRepository;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class FlightItineraryService extends BaseService
{
    protected UploadRepository $uploadRepository;
    protected ReserveRepository $reserveRepository;
    public function __construct(
        FlightItineraryRepository $flightItineraryRepository,
        UploadRepository $uploadRepository,
        ReserveRepository $reserveRepository,
    ) {
        parent::__construct($flightItineraryRepository);
        $this->uploadRepository = $uploadRepository;
        $this->reserveRepository = $reserveRepository;
    }

    public function rules($action = "store"): array
    {
        return [
            'flight_reservation_id' => 'required|integer|exists:flight_reservations,id',
            'airline' => 'required|string|max:255',
            'departure_airport' => 'required|string|max:255',
            'arrival_airport' => 'required|string|max:255',
            'takeoff_departure_date' => 'required|date',
            'takeoff_arrival_date' => 'required|date',
            'return_departure_date' => 'required|date',
            'return_arrival_date' => 'required|date',
            'departure_layovers' => 'sometimes|integer|min:0',
            'return_layovers' => 'sometimes|integer|min:0',
            'flight_ticket' => 'nullable|sometimes|mimes:pdf,jpg,jpeg,png',
            'total_ticket_price' => 'sometimes|numeric|min:0',
            'status' => 'sometimes|in:pending,accepted,rejected,cancelled',
        ];
    }

    public function update(int $id, array $data)
    {
        return DB::transaction(function () use ($id, $data) {
            $flightItinerary = parent::update($id, $data);

            if ($flightItinerary) {
                $this->updateReservationStatus($flightItinerary);

                if (isset($data['flight_ticket']) && $data['flight_ticket'] instanceof UploadedFile) {
                    $this->handleFileUpload($data['flight_ticket']);
                }
            }

            return $flightItinerary;
        });
    }

    /**
     * @throws \Exception
     */
    private function updateReservationStatus($flightItinerary): void
    {
        $reservation = $flightItinerary->reservation;

        if ($reservation && $flightItinerary->status === "accepted") {
            $reservation->update([
                'status' => 'approved',
                'total_proposed_amount' => $flightItinerary->total_ticket_price
            ]);

            // Optionally, trigger event to hold funds
            $this->reserveRepository->create([
                'user_id' => $reservation->user_id,
                'department_id' => $reservation->department_id,
                'fund_id' => $reservation->fund_id,
                'reservable_id' => $flightItinerary->id,
                'reservable_type' => FlightItinerary::class,
                'total_reserved_amount' => $flightItinerary->total_ticket_price,
                'description' => $reservation->description,
            ]);

            // Send Mail here
        }
    }

    /**
     * @throws \Exception
     */
    private function handleFileUpload(UploadedFile $file): void
    {
        $uploadData = [
            'name' => "flight-itinerary-" . time() . '.' . $file->getClientOriginalExtension(),
            'mime_type' => $file->getClientMimeType(),
            'extension' => $file->getClientOriginalExtension(),
            'path' => $file->store('reservations/tickets', 'public'),
            'size' => $file->getSize(),
        ];

        $this->uploadRepository->create($uploadData);
    }
}
