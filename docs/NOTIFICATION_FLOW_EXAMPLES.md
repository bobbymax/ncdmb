# 📊 Resource Notification Flow - Real Examples

## 🎯 **Example 1: InboundInstruction (Department Assignment)**

### **Scenario:**
Officer creates an instruction and assigns it to the **"Legal Department"**

### **Flow:**

```
1. User submits form in InboundInstructions.tsx
    ↓
2. Frontend calls: instructionRepo.store(data)
    ↓
3. Backend: InboundInstructionService::store()
    ↓ [DB Transaction]
4. Creates InboundInstruction record in database
    ↓
5. Transaction COMMITS ✅
    ↓ [Observer Triggers - OUTSIDE transaction]
6. ResourceNotificationObserver detects "created" event
    ↓
7. Observer finds: InboundInstructionRepository
    ↓
8. Calls: repository->resolveNotificationRecipients(instruction, 'created')
    ↓
9. Repository logic:
   - Assignable type = "Department"
   - Finds Legal Department (ID: 5)
   - Gets: signatory_staff_id = 12
   - Gets: alternate_signatory_staff_id = 18
   - Gets: director = 24
   - Gets: created_by_id = 7
   - Returns: [12, 18, 24, 7] (4 recipients)
    ↓
10. Observer auto-builds ResourceNotificationContext:
    - repositoryClass: "InboundInstructionRepository"
    - resourceType: "inbound_instruction"
    - resourceId: 101
    - action: "created"
    - actorId: 7
    - recipients: [12, 18, 24, 7]
    - resourceData: {...}
    - metadata: {...}
    ↓
11. Dispatches ProcessResourceNotificationJob (queued, afterCommit)
    ↓
12. Frontend receives broadcast: "Preparing notifications..." (0/4)
    ↓
13. Job loads Users 12, 18, 24, 7 in ONE query
    ↓
14. Creates 4 SendResourceNotificationJob instances
    ↓
15. Dispatches as Bus::batch()
    ↓
16. Job 1: Send to User 12
    - Sends email ✅
    - Stores notification ✅
    - Broadcasts: "Sent to John Doe" (1/4) → 25%
    ↓
17. Job 2: Send to User 18
    - Sends email ✅
    - Stores notification ✅
    - Broadcasts: "Sent to Jane Smith" (2/4) → 50%
    ↓
18. Job 3: Send to User 24
    - Sends email ✅
    - Stores notification ✅
    - Broadcasts: "Sent to Bob Johnson" (3/4) → 75%
    ↓
19. Job 4: Send to User 7 (creator)
    - Sends email ✅
    - Stores notification ✅
    - Broadcasts: "Sent to Sarah Williams" (4/4) → 100%
    ↓
20. Frontend shows: "All notifications sent successfully!" 🎉
```

**Total Time:** ~2-5 seconds (all asynchronous!)

---

## 🎯 **Example 2: InboundInstruction (Group Assignment)**

### **Scenario:**
Officer assigns instruction to **"Technical Committee"** group (20 members)

### **Flow:**

```
1-5. [Same as above - create instruction, commit transaction]
    ↓
6. ResourceNotificationObserver detects "created"
    ↓
7-8. Finds InboundInstructionRepository, calls resolveNotificationRecipients()
    ↓
9. Repository logic:
   - Assignable type = "Group"
   - Finds Technical Committee (ID: 8)
   - Gets: $group->users()->pluck('id') = [1,3,5,7,9,11,13,15,17,19,21,23,25,27,29,31,33,35,37,39]
   - Adds: created_by_id = 7
   - Returns: [1,3,5,7,9,11,13,15,17,19,21,23,25,27,29,31,33,35,37,39] (20 unique recipients)
    ↓
10-11. Auto-builds context, dispatches job
    ↓
12. Frontend: "Preparing notifications..." (0/20)
    ↓
13. Loads 20 users in ONE query
    ↓
14. Creates 20 SendResourceNotificationJob instances
    ↓
15. Dispatches as batch
    ↓
16-35. Sends 20 emails + notifications
    - Each broadcasts progress: (1/20), (2/20), ... (20/20)
    ↓
36. Frontend: "All notifications sent successfully!" 🎉
```

**Total Time:** ~5-10 seconds (all asynchronous!)

---

## 🎯 **Example 3: Claim Model (New Resource)**

### **Scenario:**
You want to add notifications for the `Claim` model

### **Implementation:**

#### **Step 1: Add Trait to Model** (1 line)

```php
// app/Models/Claim.php
use App\Traits\NotifiesOnChanges;

class Claim extends Model
{
    use HasFactory, NotifiesOnChanges; // ✅
}
```

#### **Step 2: Implement Interface in Repository** (~40 lines)

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
                // Notify claim owner + HR department
                $hr = \App\Models\Department::where('code', 'HR')->first();
                $recipients = array_filter([
                    $claim->user_id,
                    $hr->signatory_staff_id ?? null,
                    $hr->director ?? null,
                ]);
                break;
                
            case 'updated':
                // Notify owner + anyone who commented
                $recipients = array_merge(
                    [$claim->user_id],
                    $claim->comments()->pluck('user_id')->toArray()
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
            'claim_amount' => 'NGN ' . number_format($claim->amount, 2),
            'claim_status' => ucfirst($claim->status),
        ];
    }
    
    public function getNotificationResourceData(Model $model): array
    {
        $claim = $model;
        return [
            'reference' => $claim->reference,
            'type' => ucfirst($claim->type),
            'amount' => 'NGN ' . number_format($claim->amount, 2),
            'status' => ucfirst($claim->status),
            'description' => Str::limit($claim->description, 150),
            'submitted_at' => $claim->created_at->format('M d, Y'),
        ];
    }
}
```

#### **Step 3: Done! Test It**

```php
// Anywhere in your code
$claim = Claim::create([
    'user_id' => 5,
    'type' => 'travel',
    'amount' => 50000,
    'description' => 'Conference travel to Lagos',
]);

// ✅ Notifications automatically sent to:
// - User 5 (claim owner)
// - HR signatory
// - HR director
```

---

## 🎯 **Example 4: Project Model**

### **Scenario:**
Project assigned to multiple departments

```php
// app/Models/Project.php
class Project extends Model
{
    use NotifiesOnChanges; // ✅
}

// app/Repositories/ProjectRepository.php
public function resolveNotificationRecipients(Model $model, string $action): array
{
    $project = $model;
    $recipients = [];
    
    switch ($action) {
        case 'created':
            // Notify project manager + all department heads
            $recipients = array_merge(
                [$project->manager_id],
                $project->departments()->pluck('director')->toArray()
            );
            break;
            
        case 'updated':
            // Notify project team + stakeholders
            $recipients = array_merge(
                $project->team()->pluck('user_id')->toArray(),
                $project->stakeholders()->pluck('user_id')->toArray()
            );
            break;
            
        case 'deleted':
            // Notify project manager + creator
            $recipients = [$project->manager_id, $project->created_by_id];
            break;
    }
    
    return array_values(array_unique(array_filter($recipients, fn($id) => $id > 0)));
}
```

---

## 📈 **Scalability:**

### **Timeline:**

```
T+0ms:     User submits form
T+50ms:    Database transaction commits
T+60ms:    Observer detects change
T+70ms:    Repository resolves recipients
T+80ms:    Context built, job dispatched
T+100ms:   HTTP response sent to user ✅
----- User sees success message -----
T+500ms:   Queue worker picks up ProcessResourceNotificationJob
T+600ms:   Loads all users from database
T+700ms:   Creates batch of SendResourceNotificationJob
T+1000ms:  First email sent → Broadcast (1/10)
T+1200ms:  Second email sent → Broadcast (2/10)
...
T+5000ms:  All emails sent → Broadcast "Complete!" 🎉
```

**User Experience:** Instant response + real-time progress updates!

---

## 🎨 **Frontend UX:**

```typescript
// User submits instruction
↓
Immediate toast: "Instruction issued successfully!" ✅
↓
2 seconds later: "Preparing notifications..." (spinner)
↓
Progress toasts:
  "Sent to John Doe" (33%)
  "Sent to Jane Smith" (66%)
  "Sent to Bob Johnson" (100%)
↓
Final toast: "All notifications sent successfully!" 🎉
```

---

## 🧪 **Testing Different Scenarios:**

### **Test 1: Single User**
```php
$instruction = InboundInstruction::create([
    'assignable_type' => 'App\Models\User',
    'assignable_id' => 5,
    'created_by_id' => 7,
]);
// Recipients: [5, 7] → 2 emails
```

### **Test 2: Small Department**
```php
$instruction = InboundInstruction::create([
    'assignable_type' => 'App\Models\Department',
    'assignable_id' => 3, // Department with 3 staff
    'created_by_id' => 7,
]);
// Recipients: [12, 18, 24, 7] → 4 emails
```

### **Test 3: Large Group**
```php
$instruction = InboundInstruction::create([
    'assignable_type' => 'App\Models\Group',
    'assignable_id' => 8, // Group with 50 members
    'created_by_id' => 7,
]);
// Recipients: [1,3,5,7,...,99,7] → 50 unique emails
```

---

## 🎓 **Best Practices:**

### **1. Keep Recipients Focused**
```php
// ✅ Good - Only relevant people
return [$claim->user_id, $claim->approver_id];

// ❌ Bad - Everyone in the company
return User::all()->pluck('id')->toArray();
```

### **2. Handle Missing Data Gracefully**
```php
// ✅ Good - Filters nulls and zeros
$recipients = array_filter([
    $dept->signatory_staff_id,
    $dept->alternate_signatory_staff_id,
    $dept->director,
]);
return array_filter($recipients, fn($id) => $id > 0);

// ❌ Bad - Might include nulls
return [$dept->signatory_staff_id, $dept->director];
```

### **3. Action-Specific Logic**
```php
// ✅ Good - Different recipients per action
switch ($action) {
    case 'created': return [approvers];
    case 'updated': return [watchers];
    case 'deleted': return [owner];
}

// ❌ Bad - Same recipients always
return [$model->user_id];
```

---

## 🎉 **Summary:**

**This architecture makes notifications:**
- ✅ Automatic
- ✅ Consistent
- ✅ Extensible
- ✅ Performant
- ✅ Maintainable

**Just add the trait, implement the interface, and you're done!** 🚀

---

**Created:** November 3, 2025  
**Status:** ✅ Production-Ready

