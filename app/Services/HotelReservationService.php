<?php

namespace App\Services;

use App\Models\HotelReservation;
use App\Repositories\HotelReservationRepository;
use App\Repositories\ReserveRepository;
use App\Repositories\UploadRepository;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class HotelReservationService extends BaseService
{
    protected UploadRepository $uploadRepository;
    protected ReserveRepository $reserveRepository;
    public function __construct(
        HotelReservationRepository $hotelReservationRepository,
        UploadRepository $uploadRepository,
        ReserveRepository $reserveRepository
    ) {
        parent::__construct($hotelReservationRepository);
        $this->uploadRepository = $uploadRepository;
        $this->reserveRepository = $reserveRepository;
    }

    public function rules($action = "store"): array
    {
        return [
            'hotel_id' => 'required|integer|exists:hotels,id',
            'fund_id' => 'required|integer|exists:funds,id',
            'staff_id' => 'sometimes|integer|min:0',
            'mandate_id' => 'sometimes|integer|min:0',
            'approval_memo' => 'sometimes|nullable|mimes:pdf|max:4096',
            'hotel_booking' => 'sometimes|nullable|mimes:pdf|max:4096',
            'cost_no_of_nights' => 'sometimes|numeric|min:0',
            'total_approved_amount' => 'sometimes|numeric|min:0',
            'check_in_date' => 'sometimes|nullable|date',
            'check_out_date' => 'sometimes|nullable|date',
            'check_in_time' => 'sometimes|nullable|date_format:H:i',
            'purpose' => 'required|string|max:255',
            'remark' => 'sometimes|nullable|string|min:3',
            'status' => 'sometimes|nullable|in:pending,in-progress,decision,approved,rejected,denied',
            'is_accepted' => 'sometimes|boolean',
        ];
    }

    public function store(array $data)
    {
        return DB::transaction(function () use ($data) {
            $hotelReservation = parent::store($data);

            if ($hotelReservation) {

                if (isset($data['approval_memo']) && $data['approval_memo'] instanceof UploadedFile) {
                    $path = $this->handleFileUpload($data['approval_memo'], 'reservations/memos', 'hotel-approval-memo-');
                    $hotelReservation->update([
                        'approval_memo_attachment' => $path,
                    ]);
                }

            }

            return $hotelReservation;
        });
    }

    public function update(int $id, array $data)
    {
        return DB::transaction(function () use ($id, $data) {
            $hotelReservation = parent::update($id, $data);

            if ($hotelReservation) {
                if (isset($data['hotel_booking']) && $data['hotel_booking'] instanceof UploadedFile) {
                    $path = $this->handleFileUpload($data['hotel_booking'], 'reservations/hotels', 'hotel-booking-ref-');
                    $hotelReservation->update([
                        'hotel_booking_attachment' => $path,
                    ]);
                }

                if ($hotelReservation->is_accepted) {
                    $hotelReservation->update([
                        'status' => 'approved'
                    ]);

                    // Optionally, trigger event to hold funds
                    $this->reserveRepository->create([
                        'user_id' => $hotelReservation->user_id,
                        'department_id' => $hotelReservation->department_id,
                        'fund_id' => $hotelReservation->fund_id,
                        'reservable_id' => $hotelReservation->id,
                        'reservable_type' => HotelReservation::class,
                        'total_reserved_amount' => $hotelReservation->total_approved_amount,
                        'description' => $hotelReservation->purpose,
                    ]);

                    // Send Mail here
                }
            }
        });
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
}
