<?php

namespace App\Services;


use App\Models\Meeting;
use App\Repositories\MeetingRepository;
use App\Repositories\ReserveRepository;
use App\Repositories\UploadRepository;
use App\Repositories\UserRepository;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class MeetingService extends BaseService
{
    protected UploadRepository $uploadRepository;
    protected ReserveRepository $reserveRepository;
    protected UserRepository $userRepository;
    public function __construct(
        MeetingRepository $meetingRepository,
        UploadRepository $uploadRepository,
        ReserveRepository $reserveRepository,
        UserRepository $userRepository
    ) {
        parent::__construct($meetingRepository);
        $this->uploadRepository = $uploadRepository;
        $this->reserveRepository = $reserveRepository;
        $this->userRepository = $userRepository;
    }

    public function rules($action = "store"): array
    {
        return [
            'room_id' => 'required|integer|exists:rooms,id',
            'fund_id' => 'sometimes|integer|min:0',
            'staff_id' => 'sometimes|integer|min:0',
            'title' => 'required|string|max:255',
            'description' => 'required|string|min:3',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'duration' => 'required|integer|min:1',
            'contact_person_name' => 'nullable|string|max:255',
            'contact_person_phone' => 'nullable|string|max:255',
            'contact_person_email' => 'nullable|string|max:255',
            'no_of_participants' => 'sometimes|integer|min:1',
            'arrangement' => 'required|string|in:cinema,banquet,meeting',
            'pa_system' => 'sometimes|boolean',
            'audio_visual_system' => 'sometimes|boolean',
            'internet' => 'sometimes|boolean',
            'tea_snacks' => 'sometimes|boolean',
            'breakfast' => 'sometimes|boolean',
            'lunch' => 'sometimes|boolean',
            'approval_memo' => 'sometimes|nullable|mimes:pdf',
            'reason_for_denial' => 'sometimes|nullable|string|min:3',
            'total_approved_amount' => 'sometimes|numeric|min:1',
            'status' => 'sometimes|string|in:pending,registered,processing,approved,rejected,cancelled,declined',
            'has_accepted' => 'sometimes|boolean',
            'closed' => 'sometimes|boolean',
            'attendees' => 'sometimes|array',
        ];
    }

    public function store(array $data)
    {
        return DB::transaction(function () use ($data) {
            $meeting = parent::store($data);

            if ($meeting) {
                if (isset($data['approval_memo']) && $data['approval_memo'] instanceof UploadedFile) {
                    $path = $this->handleFileUpload($data['approval_memo'], 'reservations/meetings', 'meeting-memo-');
                    $meeting->update(['approval_memo_attachment' => $path]);
                }
            }
        });
    }

    public function update(int $id, array $data)
    {
        return DB::transaction(function () use ($id, $data) {
            $meeting = parent::update($id, $data);

            if ($meeting) {
                if (isset($data['approval_memo']) && $data['approval_memo'] instanceof UploadedFile) {
                    $path = $this->handleFileUpload($data['approval_memo'], 'reservations/meetings', 'meeting-memo-');
                    $meeting->update(['approval_memo_attachment' => $path]);
                }

                if ($meeting->status === "confirm" && !$meeting->has_accepted) {

                    if ($meeting->fund_id > 0) {
                        // Optionally, trigger event to hold funds
                        $this->reserveRepository->create([
                            'user_id' => $meeting->user_id,
                            'department_id' => $meeting->department_id,
                            'fund_id' => $meeting->fund_id,
                            'reservable_id' => $meeting->id,
                            'reservable_type' => Meeting::class,
                            'total_reserved_amount' => $meeting->total_approved_amount,
                            'description' => $meeting->title,
                        ]);
                        // Send Mail here
                    }

                    $meeting->update(['has_accepted' => true]);
                } elseif ($meeting->status === "approved") {
                    // Do Something here... Send out mail
                    // Set Calendar

                    if (isset($data['attendees'])) {
                        foreach ($data['attendees'] as $attendee) {
                            $user = $this->userRepository->find($attendee['value']);
                            $meeting->attendees()->save($user);
                        }
                    }
                }
            }

            return $meeting;
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
