# Resource Notification System

## Overview

A **fully automated, repository-aware notification system** that handles email and database notifications for all resource CRUD operations with real-time progress broadcasting to the frontend.

## Key Features

✅ **Zero Boilerplate** - Just add a trait to your model!  
✅ **Repository-Aware** - Each repository defines its own notification logic  
✅ **Observer-Based** - Automatically detects created/updated/deleted events  
✅ **Transaction-Safe** - Notifications dispatch AFTER database commit  
✅ **Real-time Broadcasting** - Progress updates to frontend via Pusher/Reverb  
✅ **Extensible** - Add new resources in 3 steps  

## Architecture

```
Model (with NotifiesOnChanges trait)
    ↓
[created/updated/deleted event fires]
    ↓
ResourceNotificationObserver (universal observer)
    ↓
Determines Repository Class (InboundInstructionRepository, etc.)
    ↓
Calls Repository::resolveNotificationRecipients($model, $action)
    ↓
Repository returns [user IDs] based on business logic
    ↓
Observer auto-builds ResourceNotificationContext
    ↓
ResourceNotificationService::notify()
    ↓
ProcessResourceNotificationJob (queued, afterCommit)
    ↓
SendResourceNotificationJob (per recipient, queued)
    ↓
Email + Database Notification + Real-time Progress Broadcast
```

## Components

### 1. **ResourceNotificationContext** (DTO)
**Location:** `app/DTOs/ResourceNotificationContext.php`

Strongly-typed context object that carries all notification data.

**Properties:**
- `repositoryClass` - e.g., "InboundRepository"
- `resourceType` - e.g., "inbound"
- `resourceId` - The model ID
- `action` - created, updated, deleted, assigned
- `actorId` - Who performed the action
- `recipients` - Array of user IDs to notify
- `resourceData` - Snapshot of resource data
- `metadata` - Additional context

### 2. **ResourceNotificationService**
**Location:** `app/Services/ResourceNotificationService.php`

Main entry point for sending notifications.

**Key Methods:**
- `notify(ResourceNotificationContext $context)` - Dispatches notification job
- `resolveInboundInstructionRecipients(InboundInstruction $instruction)` - Resolves recipients based on assignment type

**Recipient Resolution Logic:**
```php
Department → [signatory_staff_id, alternate_signatory_staff_id, director] + creator
Group → All group users + creator
User → [assignable_id] + creator
```

### 3. **ProcessResourceNotificationJob**
**Location:** `app/Jobs/ProcessResourceNotificationJob.php`

Processes the notification context and creates individual jobs for each recipient.

**Features:**
- Validates context
- Loads all users in batch
- Creates individual notification jobs
- Dispatches using `Bus::batch()`
- Broadcasts initial progress

### 4. **SendResourceNotificationJob**
**Location:** `app/Jobs/SendResourceNotificationJob.php`

Sends notification to a single recipient.

**Actions:**
- Sends email (queued)
- Stores database notification
- Broadcasts progress update

### 5. **ResourceNotificationProgress** (Event)
**Location:** `app/Events/ResourceNotificationProgress.php`

Broadcasting event that sends real-time updates to the frontend.

**Broadcasts on:** `resource.{resourceType}.{resourceId}`

**Data:**
```json
{
  "resource_type": "inbound_instruction",
  "resource_id": 123,
  "current": 3,
  "total": 10,
  "percentage": 30,
  "message": "Sent to John Doe",
  "error": false,
  "completed": false
}
```

### 6. **ResourceActionMail**
**Location:** `app/Mail/ResourceActionMail.php`

Mailable class for email notifications.

**Template:** `resources/views/emails/resource-action.blade.php`

### 7. **ResourceActionNotification**
**Location:** `app/Notifications/ResourceActionNotification.php`

Database notification (stored in `notifications` table).

## Quick Start

### Add Notifications to Any Model (3 Steps)

#### Step 1: Add Trait to Model

```php
// app/Models/InboundInstruction.php
use App\Traits\NotifiesOnChanges;

class InboundInstruction extends Model
{
    use HasFactory, NotifiesOnChanges; // ✅ That's it!
}
```

#### Step 2: Implement Interface in Repository

```php
// app/Repositories/InboundInstructionRepository.php
use App\Contracts\ProvidesNotificationRecipients;
use Illuminate\Database\Eloquent\Model;

class InboundInstructionRepository extends BaseRepository implements ProvidesNotificationRecipients
{
    public function resolveNotificationRecipients(Model $model, string $action): array
    {
        $instruction = $model;
        $recipients = [];
        
        // Your business logic here
        switch (class_basename($instruction->assignable_type)) {
            case 'Department':
                $dept = \App\Models\Department::find($instruction->assignable_id);
                $recipients = array_filter([
                    $dept->signatory_staff_id,
                    $dept->alternate_signatory_staff_id,
                    $dept->director,
                ]);
                break;
            case 'Group':
                $group = \App\Models\Group::find($instruction->assignable_id);
                $recipients = $group->users()->pluck('id')->toArray();
                break;
            case 'User':
                $recipients = [$instruction->assignable_id];
                break;
        }
        
        // Add creator
        $recipients[] = $instruction->created_by_id;
        
        return array_values(array_unique(array_filter($recipients, fn($id) => $id > 0)));
    }
    
    public function getNotificationMetadata(Model $model): array
    {
        $instruction = $model;
        return [
            'inbound_ref' => $instruction->inbound->ref_no ?? 'N/A',
            'inbound_from' => $instruction->inbound->from_name ?? 'Unknown',
        ];
    }
    
    public function getNotificationResourceData(Model $model): array
    {
        $instruction = $model;
        return [
            'instruction_type' => ucfirst(str_replace('_', ' ', $instruction->instruction_type)),
            'instruction_text' => Str::limit($instruction->instruction_text, 200),
            'priority' => ucfirst($instruction->priority),
            'due_date' => $instruction->due_date ? date('M d, Y', strtotime($instruction->due_date)) : 'Not set',
        ];
    }
}
```

#### Step 3: Done! ✅

Notifications will automatically be sent when the model is created, updated, or deleted!

### Frontend Integration

```typescript
// In your React component
useEffect(() => {
  const channel = window.Echo?.private(`resource.inbound_instruction.${resourceId}`);
  
  if (!channel) return;
  
  channel.listen('.ResourceNotificationProgress', (data: any) => {
    if (data.error) {
      toast.error(data.message);
    } else if (data.completed) {
      toast.success('All notifications sent successfully!');
    } else {
      toast.info(data.message, {
        progress: data.percentage / 100,
      });
    }
  });
  
  return () => {
    channel.stopListening('.ResourceNotificationProgress');
  };
}, [resourceId]);
```

## Broadcasting Channel Authorization

**Location:** `routes/channels.php`

```php
Broadcast::channel('resource.{resourceType}.{resourceId}', function ($user, string $resourceType, int $resourceId) {
    // Check if user has access to this resource
    return $user !== null;
});
```

## Queue Configuration

All notification jobs use the **`notifications`** queue.

**Running the queue worker:**
```bash
php artisan queue:work --queue=notifications
```

## Features

✅ **Repository-Aware** - Knows which repository triggered the notification
✅ **Action-Aware** - Handles different action types (created, updated, deleted, assigned)
✅ **Queued** - Non-blocking, uses `afterCommit()` and `afterResponse()`
✅ **Broadcasting** - Real-time progress updates to frontend
✅ **Batching** - Efficient bulk notifications using `Bus::batch()`
✅ **Retry Logic** - 3 attempts with exponential backoff [5s, 15s, 30s]
✅ **Error Handling** - Comprehensive logging and graceful failure
✅ **Extensible** - Easy to add new resource types

## Adding Notifications to a New Resource (3 Steps)

### Step 1: Add Trait to Model

```php
// app/Models/Claim.php
use App\Traits\NotifiesOnChanges;

class Claim extends Model
{
    use HasFactory, NotifiesOnChanges; // ✅ Done!
}
```

### Step 2: Implement Interface in Repository

```php
// app/Repositories/ClaimRepository.php
use App\Contracts\ProvidesNotificationRecipients;
use Illuminate\Database\Eloquent\Model;

class ClaimRepository extends BaseRepository implements ProvidesNotificationRecipients
{
    public function resolveNotificationRecipients(Model $model, string $action): array
    {
        $claim = $model;
        $recipients = [];
        
        switch ($action) {
            case 'created':
                // Notify claim owner and department approvers
                $recipients = array_filter([
                    $claim->user_id,
                    $claim->department->signatory_staff_id ?? null,
                    $claim->department->director ?? null,
                ]);
                break;
                
            case 'updated':
                // Notify owner and watchers
                $recipients = array_merge(
                    [$claim->user_id],
                    $claim->watchers()->pluck('user_id')->toArray()
                );
                break;
                
            case 'deleted':
                // Notify owner only
                $recipients = [$claim->user_id];
                break;
        }
        
        return array_values(array_unique(array_filter($recipients, fn($id) => $id > 0)));
    }
    
    public function getNotificationMetadata(Model $model): array
    {
        $claim = $model;
        return [
            'claim_type' => $claim->type,
            'amount' => number_format($claim->amount, 2),
            'status' => $claim->status,
        ];
    }
    
    public function getNotificationResourceData(Model $model): array
    {
        $claim = $model;
        return [
            'reference' => $claim->reference,
            'type' => $claim->type,
            'amount' => number_format($claim->amount, 2),
            'submitted_at' => $claim->created_at->format('M d, Y'),
        ];
    }
}
```

### Step 3: Add Frontend Listener (Optional)

```typescript
// In your React component
useEffect(() => {
  const channel = window.Echo?.private(`resource.claim.${claimId}`);
  
  channel?.listen('.ResourceNotificationProgress', (data: any) => {
    if (data.completed) {
      toast.success('All notifications sent!');
    } else if (data.error) {
      toast.error(data.message);
    } else {
      toast.info(data.message, { progress: data.percentage / 100 });
    }
  });
  
  return () => {
    channel?.stopListening('.ResourceNotificationProgress');
  };
}, [claimId]);
```

### That's It! ✅

Your model will now **automatically send notifications** on create/update/delete!

## Logging

All components log extensively using Laravel's logging system:

- **Info:** Normal operations and progress
- **Warning:** Non-critical issues (missing recipients, etc.)
- **Error:** Critical failures with stack traces

**View logs:**
```bash
tail -f storage/logs/laravel.log
```

## Testing

### Test Notification Dispatch

```bash
php artisan tinker
```

```php
use App\DTOs\ResourceNotificationContext;
use App\Services\ResourceNotificationService;

$context = new ResourceNotificationContext(
    repositoryClass: 'TestRepository',
    resourceType: 'test',
    resourceId: 1,
    action: 'created',
    actorId: 1,
    recipients: [1, 2, 3],
    resourceData: ['test' => 'data'],
    metadata: []
);

app(ResourceNotificationService::class)->notify($context);
```

### Monitor Queue

```bash
php artisan queue:work --queue=notifications --verbose
```

## Performance Considerations

1. **Batch Loading** - Users are loaded in a single query
2. **Queued Jobs** - Non-blocking, runs asynchronously
3. **Job Batching** - Uses Laravel's Bus::batch() for efficiency
4. **afterCommit/afterResponse** - Jobs dispatched after transaction commit and HTTP response

## Troubleshooting

### Notifications Not Sending

1. Check queue worker is running: `php artisan queue:work`
2. Check logs: `storage/logs/laravel.log`
3. Verify recipients are valid user IDs
4. Test database connection

### Broadcasting Not Working

1. Verify Pusher/Reverb configuration in `.env`
2. Check channel authorization in `routes/channels.php`
3. Test Echo connection in browser console: `window.Echo`
4. Verify frontend is subscribed to correct channel

### Jobs Failing

1. Check retry attempts in logs
2. Verify email configuration (SMTP)
3. Check user exists for each recipient ID
4. Increase job timeout if needed

## Future Enhancements

- [ ] SMS notifications via SMS channel
- [ ] Push notifications for mobile
- [ ] Notification preferences per user
- [ ] Digest mode (batch notifications)
- [ ] Rich notification templates
- [ ] Analytics and tracking
- [ ] Rate limiting for notifications

---

**Created:** November 3, 2025
**Author:** AI Assistant
**Version:** 1.0.0

