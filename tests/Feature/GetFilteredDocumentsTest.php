<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Document;
use App\Models\DocumentDraft;
use App\Models\Group;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class GetFilteredDocumentsTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        // Create departments
        $this->directorate = Department::factory()->create(['type' => 'directorate']);
        $this->division = Department::factory()->create(['type' => 'division', 'parentId' => $this->directorate->id]);
        $this->department = Department::factory()->create(['type' => 'department', 'parentId' => $this->division->id]);

        // Create roles
        $this->basicRole = Role::factory()->create(['access_level' => 'basic']);
        $this->operativeRole = Role::factory()->create(['access_level' => 'operative']);
        $this->controlRole = Role::factory()->create(['access_level' => 'control']);
        $this->commandRole = Role::factory()->create(['access_level' => 'command']);
        $this->sovereignRole = Role::factory()->create(['access_level' => 'sovereign']);

        // Create users
        $this->basicUser = User::factory()->create(['role_id' => $this->basicRole->id, 'department_id' => $this->department->id]);
        $this->operativeUser = User::factory()->create(['role_id' => $this->operativeRole->id, 'department_id' => $this->department->id]);
        $this->controlUser = User::factory()->create(['role_id' => $this->controlRole->id, 'department_id' => $this->division->id]);
        $this->commandUser = User::factory()->create(['role_id' => $this->commandRole->id, 'department_id' => $this->directorate->id]);
        $this->sovereignUser = User::factory()->create(['role_id' => $this->sovereignRole->id]);

        // Create documents
        $this->basicDocument = Document::factory()->create(['user_id' => $this->basicUser->id, 'department_id' => $this->department->id]);
        $this->operativeDocument = Document::factory()->create(['user_id' => $this->operativeUser->id, 'department_id' => $this->department->id]);
        $this->controlDocument = Document::factory()->create(['user_id' => $this->controlUser->id, 'department_id' => $this->division->id]);
        $this->commandDocument = Document::factory()->create(['user_id' => $this->commandUser->id, 'department_id' => $this->directorate->id]);
        $this->sovereignDocument = Document::factory()->create();

        // Create groups
        $this->group = Group::factory()->create();
        $this->basicUser->groups()->attach($this->group->id);

        // Create drafts
        DocumentDraft::factory()->create(['document_id' => $this->operativeDocument->id, 'group_id' => $this->group->id, 'department_id' => $this->department->id]);
    }
    /**
     * A basic feature test example.
     */
    #[Test]
    public function basic_user_can_only_see_their_documents()
    {
        $this->actingAs($this->basicUser);
        $response = $this->getJson(route('documents.index'));

        $response->assertJsonCount(1);
        $response->assertJsonFragment(['id' => $this->basicDocument->id]);
    }

    #[Test]
    public function operative_user_can_see_documents_in_their_department()
    {
        $this->actingAs($this->operativeUser);
        $response = $this->getJson(route('documents.index'));

        $response->assertJsonCount(2);
        $response->assertJsonFragment(['id' => $this->operativeDocument->id]);
        $response->assertJsonFragment(['id' => $this->basicDocument->id]);
    }

    #[Test]
    public function control_user_can_see_documents_in_their_division()
    {
        $this->actingAs($this->controlUser);
        $response = $this->getJson(route('documents.index'));

        $response->assertJsonCount(1);
        $response->assertJsonFragment(['id' => $this->controlDocument->id]);
    }

    #[Test]
    public function command_user_can_see_documents_in_their_directorate()
    {
        $this->actingAs($this->commandUser);
        $response = $this->getJson(route('documents.index'));

        dd($response->json());

//        $response->assertJsonCount(1);
//        $response->assertJsonFragment(['id' => $this->commandDocument->id]);
    }

    #[Test]
    public function sovereign_user_can_see_all_documents()
    {
        $this->actingAs($this->sovereignUser);
        $response = $this->getJson(route('documents.index'));

        $response->assertJsonCount(5);
    }
}
