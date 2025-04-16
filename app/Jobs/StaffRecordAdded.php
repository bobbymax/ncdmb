<?php

namespace App\Jobs;

use App\Mail\NewStaffRecordCreated;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class StaffRecordAdded implements ShouldQueue
{
    use Dispatchable, Queueable, InteractsWithQueue, SerializesModels;

    protected User $user;

    /**
     * Create a new job instance.
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * Ensure the database connection is alive before executing.
     */
    protected function ensureDbConnection(): void
    {
        try {
            DB::connection()->getPdo(); // Test current PDO connection
        } catch (\Exception $e) {
            DB::reconnect(); // Force reconnection if it's dead
        }
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $this->ensureDbConnection();
        try {
            Mail::to($this->user->email)->queue(new NewStaffRecordCreated($this->user));
        } catch (\Exception $e) {
            Log::error('Error sending record notifications' . $e->getMessage());
        }
    }
}
