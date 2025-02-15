<?php

namespace App\Services;

use App\Models\FlightReservation;
use App\Repositories\FlightReservationRepository;
use App\Repositories\UploadRepository;
use Illuminate\Support\Facades\DB;

class FlightReservationService extends BaseService
{
    protected UploadRepository $uploadRepository;

    public function __construct(
        FlightReservationRepository $flightReservationRepository,
        UploadRepository $uploadRepository
    ) {
        parent::__construct($flightReservationRepository);
        $this->uploadRepository = $uploadRepository;
    }

    public function rules($action = "store"): array
    {
        return [
            'fund_id' => 'required|integer|exists:funds,id',
            'purpose_for_trip' => 'required|string|max:255',
            'total_proposed_amount' => 'sometimes|numeric|min:0',
            'takeoff' => 'required|string|max:255',
            'destination' => 'required|string|max:255',
            'departure_date' => 'required|date',
            'return_date' => 'required|date',
            'description' => 'required|string|min:3',
            'approval_memo' => 'sometimes|mimes:pdf,jpg,jpeg,png',
            'visa' => 'sometimes|mimes:pdf,jpg,jpeg,png',
            'data_page' => 'sometimes|mimes:pdf,jpg,jpeg,png',
            'type' => 'required|string|in:staff,third-party',
            'status' => 'sometimes|string|in:pending,registered,in-progress,itinerary-updated,staff-decision,approved,rejected,cancelled'
        ];
    }

    public function store(array $data)
    {
        return DB::transaction(function () use ($data) {
            $flightReservation = parent::store($data);
            if ($flightReservation) {
                $documents = $this->handleFileUploads($data, [
                    'approval_memo' => 'reservations/flights',
                    'visa' => 'reservations/visas',
                    'data_page' => 'reservations/data-pages',
                ]);

                foreach ($documents as $document) {
                    $newPath = $document['storagePath'];
//                    unset($document['storagePath']);
                    $this->uploadRepository->create([
                        ...$document,
                        'path' => $newPath,
                        'uploadable_id' => $flightReservation->id,
                        'uploadable_type' => FlightReservation::class,
                    ]);
                }

                $flightReservation->update($this->getDocumentPaths($documents));
            }

            return $flightReservation;
        });
    }

    public function update(int $id, array $data, $parsed = true)
    {
        return DB::transaction(function () use ($id, $data) {
            $flightReservation = parent::update($id, $data);
            if ($flightReservation) {
                $documents = $this->handleFileUploads($data, [
                    'approval_memo' => 'reservations/flights',
                    'visa' => 'reservations/visas',
                    'data_page' => 'reservations/data-pages',
                ]);

                foreach ($documents as $document) {
                    $newPath = $document['storagePath'];
//                    unset($document['storagePath']);
                    $this->uploadRepository->create([
                        ...$document,
                        'path' => $newPath,
                        'uploadable_id' => $flightReservation->id,
                        'uploadable_type' => FlightReservation::class,
                    ]);
                }

                $flightReservation->update($this->getDocumentPaths($documents));
            }
        });
    }

    protected function handleFileUploads(array $data, array $fileKeys): array
    {
        $documents = [];
        foreach ($fileKeys as $key => $path) {
            if (isset($data[$key]) && $data[$key] instanceof \Illuminate\Http\UploadedFile) {
                $file = $data[$key];
                $name = "flight-reservation-{$key}-" . time() . ".{$file->getClientOriginalExtension()}";
                $mime_type = $file->getClientMimeType();
                $extension = $file->getClientOriginalExtension();
                $storagePath = $file->store($path, 'public');
                $size = $file->getSize();

                $documents[] = compact('name', 'storagePath', 'mime_type', 'extension', 'size');
            }
        }
        return $documents;
    }

    protected function getDocumentPaths(array $documents): array
    {
        $paths = [];
        foreach ($documents as $document) {
            if (strpos($document['name'], 'approval-memo') !== false) {
                $paths['approval_memo_path'] = $document['storagePath'];
            } elseif (strpos($document['name'], 'visa') !== false) {
                $paths['visa_path'] = $document['storagePath'];
            } elseif (strpos($document['name'], 'data-page') !== false) {
                $paths['data_page_path'] = $document['storagePath'];
            }
        }
        return $paths;
    }
}
