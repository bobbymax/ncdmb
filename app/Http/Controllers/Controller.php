<?php

namespace App\Http\Controllers;

use App\Handlers\DataNotFound;
use App\Handlers\RecordCreationUnsuccessful;
use App\Handlers\ValidationErrors;
use App\Services\BaseService;
use App\Traits\ApiResponse;
use App\Traits\ResourceContainer;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Validator;

/**
 * @OA\Info(
 *     title="NCDMB API Documentation",
 *     version="1.0.0",
 *     description="Comprehensive API documentation for the NCDMB Document Management System. This API provides endpoints for document management, workflow automation, procurement, legal processes, and more.",
 *     @OA\Contact(
 *         email="support@ncdmb.gov.ng"
 *     )
 * )
 *
 * @OA\Server(
 *     url=L5_SWAGGER_CONST_HOST,
 *     description="API Server"
 * )
 *
 * @OA\SecurityScheme(
 *     securityScheme="sanctum",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT",
 *     description="Laravel Sanctum Bearer Token Authentication. Get your token by logging in via /api/login endpoint."
 * )
 *
 * @OA\Tag(
 *     name="Authentication",
 *     description="Authentication and authorization endpoints"
 * )
 *
 * @OA\Tag(
 *     name="Users",
 *     description="User management endpoints"
 * )
 *
 * @OA\Tag(
 *     name="Documents",
 *     description="Document management endpoints"
 * )
 *
 * @OA\Tag(
 *     name="Projects",
 *     description="Project management endpoints"
 * )
 *
 * @OA\Tag(
 *     name="Procurement",
 *     description="Procurement and bidding endpoints"
 * )
 *
 * @OA\Tag(
 *     name="Legal",
 *     description="Legal review and clearance endpoints"
 * )
 *
 * @OA\Tag(
 *     name="Roles",
 *     description="Role management endpoints"
 * )
 *
 * @OA\Tag(
 *     name="Groups",
 *     description="Group management endpoints"
 * )
 *
 * @OA\Schema(
 *     schema="Error",
 *     type="object",
 *     @OA\Property(property="message", type="string", example="Error message"),
 *     @OA\Property(property="errors", type="object", example={})
 * )
 *
 * @OA\Schema(
 *     schema="Success",
 *     type="object",
 *     @OA\Property(property="success", type="boolean", example=true),
 *     @OA\Property(property="data", type="object"),
 *     @OA\Property(property="message", type="string", example="Operation successful")
 * )
 *
 * @OA\Schema(
 *     schema="DocumentRequest",
 *     type="object",
 *     required={"user_id", "department_id", "document_category_id", "workflow_id", "document_type_id", "title", "description", "ref", "status"},
 *     @OA\Property(property="user_id", type="integer", example=1),
 *     @OA\Property(property="department_id", type="integer", example=1),
 *     @OA\Property(property="document_category_id", type="integer", example=1),
 *     @OA\Property(property="workflow_id", type="integer", example=1),
 *     @OA\Property(property="document_type_id", type="integer", example=1),
 *     @OA\Property(property="vendor_id", type="integer", example=null, nullable=true),
 *     @OA\Property(property="documentable_id", type="integer", example=1),
 *     @OA\Property(property="documentable_type", type="string", example="App\\Models\\Project"),
 *     @OA\Property(property="title", type="string", example="Project Proposal Document"),
 *     @OA\Property(property="description", type="string", example="Detailed description of the document"),
 *     @OA\Property(property="ref", type="string", example="DOC-2024-001"),
 *     @OA\Property(property="status", type="string", enum={"pending", "approved", "rejected"}, example="pending"),
 *     @OA\Property(property="is_archived", type="boolean", example=false, nullable=true)
 * )
 *
 * @OA\Schema(
 *     schema="UserRequest",
 *     type="object",
 *     required={"firstname", "surname", "email", "grade_level_id", "department_id", "role_id", "location_id", "gender", "type"},
 *     @OA\Property(property="staff_no", type="string", example="EMP001", nullable=true, maxLength=8),
 *     @OA\Property(property="firstname", type="string", example="John", maxLength=100),
 *     @OA\Property(property="middlename", type="string", example="Michael", nullable=true, maxLength=100),
 *     @OA\Property(property="surname", type="string", example="Doe", maxLength=100),
 *     @OA\Property(property="email", type="string", format="email", example="john.doe@example.com", maxLength=255),
 *     @OA\Property(property="grade_level_id", type="integer", example=1),
 *     @OA\Property(property="department_id", type="integer", example=1),
 *     @OA\Property(property="role_id", type="integer", example=1),
 *     @OA\Property(property="location_id", type="integer", example=1),
 *     @OA\Property(property="gender", type="string", enum={"male", "female"}, example="male"),
 *     @OA\Property(property="type", type="string", enum={"permanent", "contract", "adhoc", "secondment", "support", "admin"}, example="permanent"),
 *     @OA\Property(property="job_title", type="string", example="Software Developer", nullable=true, maxLength=255),
 *     @OA\Property(property="date_joined", type="string", format="date", example="2024-01-15", nullable=true),
 *     @OA\Property(property="avatar", type="string", example=null, nullable=true, maxLength=255),
 *     @OA\Property(property="default_page_id", type="integer", example=1, nullable=true),
 *     @OA\Property(property="groups", type="array", @OA\Items(type="object"), example={{"value": 1}, {"value": 2}}, nullable=true)
 * )
 *
 * @OA\Schema(
 *     schema="RoleRequest",
 *     type="object",
 *     required={"name", "slots", "access_level", "department_id"},
 *     @OA\Property(property="name", type="string", example="Administrator", maxLength=255),
 *     @OA\Property(property="slots", type="integer", example=5, minimum=1),
 *     @OA\Property(property="access_level", type="string", enum={"basic", "operative", "control", "command", "sovereign", "system"}, example="basic"),
 *     @OA\Property(property="department_id", type="integer", example=1),
 *     @OA\Property(property="issued_date", type="string", format="date", example="2024-01-15", nullable=true),
 *     @OA\Property(property="expired_date", type="string", format="date", example="2025-01-15", nullable=true)
 * )
 *
 * @OA\Schema(
 *     schema="GroupRequest",
 *     type="object",
 *     required={"name"},
 *     @OA\Property(property="name", type="string", example="IT Department")
 * )
 *
 * @OA\Schema(
 *     schema="ProjectRequest",
 *     type="object",
 *     required={"user_id", "department_id", "threshold_id", "project_category_id", "title", "type", "status", "project_type", "priority"},
 *     @OA\Property(property="user_id", type="integer", example=1),
 *     @OA\Property(property="department_id", type="integer", example=1),
 *     @OA\Property(property="threshold_id", type="integer", example=1),
 *     @OA\Property(property="project_category_id", type="integer", example=1),
 *     @OA\Property(property="title", type="string", example="Infrastructure Development Project", maxLength=500),
 *     @OA\Property(property="description", type="string", example="Detailed project description", nullable=true),
 *     @OA\Property(property="total_proposed_amount", type="number", example=1000000.00, nullable=true, minimum=0),
 *     @OA\Property(property="sub_total_amount", type="number", example=900000.00, nullable=true, minimum=0),
 *     @OA\Property(property="service_charge_percentage", type="integer", example=5, nullable=true, minimum=0, maximum=100),
 *     @OA\Property(property="markup_amount", type="number", example=50000.00, nullable=true, minimum=0),
 *     @OA\Property(property="vat_amount", type="number", example=50000.00, nullable=true, minimum=0),
 *     @OA\Property(property="proposed_start_date", type="string", format="date", example="2024-01-15", nullable=true),
 *     @OA\Property(property="proposed_end_date", type="string", format="date", example="2024-12-31", nullable=true),
 *     @OA\Property(property="type", type="string", enum={"staff", "third-party"}, example="staff"),
 *     @OA\Property(property="status", type="string", enum={"pending", "registered", "approved", "denied", "kiv", "discussed"}, example="pending"),
 *     @OA\Property(property="project_type", type="string", enum={"capital", "operational", "maintenance", "research", "infrastructure"}, example="infrastructure"),
 *     @OA\Property(property="priority", type="string", enum={"critical", "high", "medium", "low"}, example="high"),
 *     @OA\Property(property="strategic_alignment", type="string", example="Strategic alignment notes", nullable=true, maxLength=1000),
 *     @OA\Property(property="fund_id", type="integer", example=1, nullable=true),
 *     @OA\Property(property="budget_year", type="string", example="2024", nullable=true, maxLength=20),
 *     @OA\Property(property="variation_amount", type="number", example=0.00, nullable=true),
 *     @OA\Property(property="total_approved_amount", type="number", example=1000000.00, nullable=true, minimum=0)
 * )
 *
 * @OA\Schema(
 *     schema="ProjectBidInvitationRequest",
 *     type="object",
 *     required={"project_id", "title", "submission_deadline", "opening_date"},
 *     @OA\Property(property="project_id", type="integer", example=1),
 *     @OA\Property(property="title", type="string", example="Infrastructure Development Bid", maxLength=500),
 *     @OA\Property(property="description", type="string", example="Bid invitation description", nullable=true),
 *     @OA\Property(property="technical_specifications", type="string", example="Technical requirements", nullable=true),
 *     @OA\Property(property="scope_of_work", type="string", example="Scope details", nullable=true),
 *     @OA\Property(property="deliverables", type="string", example="Expected deliverables", nullable=true),
 *     @OA\Property(property="terms_and_conditions", type="string", example="Terms and conditions", nullable=true),
 *     @OA\Property(property="required_documents", type="array", @OA\Items(type="string"), example={"tax_clearance", "company_registration"}, nullable=true),
 *     @OA\Property(property="eligibility_criteria", type="array", @OA\Items(type="string"), example={"5_years_experience", "certified_contractor"}, nullable=true),
 *     @OA\Property(property="bid_security_required", type="boolean", example=true),
 *     @OA\Property(property="bid_security_amount", type="number", example=50000.00, nullable=true, minimum=0),
 *     @OA\Property(property="bid_security_validity_days", type="integer", example=90, nullable=true, minimum=1),
 *     @OA\Property(property="estimated_contract_value", type="number", example=1000000.00, nullable=true, minimum=0),
 *     @OA\Property(property="advertisement_date", type="string", format="date", example="2024-01-15", nullable=true),
 *     @OA\Property(property="pre_bid_meeting_date", type="string", format="date", example="2024-01-20", nullable=true),
 *     @OA\Property(property="pre_bid_meeting_location", type="string", example="Conference Room A", nullable=true, maxLength=500),
 *     @OA\Property(property="submission_deadline", type="string", format="date-time", example="2024-02-15T17:00:00Z"),
 *     @OA\Property(property="bid_validity_days", type="integer", example=120, nullable=true, minimum=1),
 *     @OA\Property(property="opening_date", type="string", format="date-time", example="2024-02-16T10:00:00Z"),
 *     @OA\Property(property="opening_location", type="string", example="Main Hall", nullable=true, maxLength=500),
 *     @OA\Property(property="evaluation_criteria", type="array", @OA\Items(type="object"), nullable=true),
 *     @OA\Property(property="technical_weight", type="number", example=60.00, nullable=true, minimum=0, maximum=100),
 *     @OA\Property(property="financial_weight", type="number", example=40.00, nullable=true, minimum=0, maximum=100),
 *     @OA\Property(property="published_newspapers", type="array", @OA\Items(type="string"), nullable=true),
 *     @OA\Property(property="published_bpp_portal", type="boolean", example=true),
 *     @OA\Property(property="status", type="string", enum={"draft", "published", "closed", "cancelled"}, example="draft", nullable=true)
 * )
 *
 * @OA\Schema(
 *     schema="ProjectBidRequest",
 *     type="object",
 *     required={"project_id", "bid_invitation_id", "vendor_id", "bid_amount"},
 *     @OA\Property(property="project_id", type="integer", example=1),
 *     @OA\Property(property="bid_invitation_id", type="integer", example=1),
 *     @OA\Property(property="vendor_id", type="integer", example=1),
 *     @OA\Property(property="bid_amount", type="number", example=950000.00, minimum=0),
 *     @OA\Property(property="bid_currency", type="string", example="NGN", nullable=true, maxLength=10),
 *     @OA\Property(property="submitted_at", type="string", format="date-time", example="2024-02-10T14:30:00Z", nullable=true),
 *     @OA\Property(property="submission_method", type="string", enum={"physical", "electronic", "hybrid"}, example="electronic", nullable=true),
 *     @OA\Property(property="received_by", type="integer", example=1, nullable=true),
 *     @OA\Property(property="bid_security_submitted", type="boolean", example=true),
 *     @OA\Property(property="bid_security_type", type="string", enum={"bank_guarantee", "insurance_bond", "cash"}, example="bank_guarantee", nullable=true),
 *     @OA\Property(property="bid_security_reference", type="string", example="BG-2024-001", nullable=true, maxLength=100),
 *     @OA\Property(property="bid_documents", type="array", @OA\Items(type="object"), nullable=true),
 *     @OA\Property(property="status", type="string", enum={"submitted", "opened", "responsive", "non_responsive", "under_evaluation", "evaluated", "disqualified", "recommended", "awarded", "not_awarded"}, example="submitted", nullable=true)
 * )
 *
 * @OA\Schema(
 *     schema="ProjectBidEvaluationRequest",
 *     type="object",
 *     required={"project_bid_id", "evaluation_type"},
 *     @OA\Property(property="project_bid_id", type="integer", example=1),
 *     @OA\Property(property="evaluator_id", type="integer", example=1, nullable=true),
 *     @OA\Property(property="evaluation_type", type="string", enum={"administrative", "technical", "financial", "post_qualification"}, example="technical"),
 *     @OA\Property(property="evaluation_date", type="string", format="date", example="2024-02-20", nullable=true),
 *     @OA\Property(property="criteria", type="array", @OA\Items(type="object"), nullable=true),
 *     @OA\Property(property="total_score", type="number", example=85.50, nullable=true, minimum=0, maximum=100),
 *     @OA\Property(property="pass_fail", type="string", enum={"pass", "fail", "conditional"}, example="pass", nullable=true),
 *     @OA\Property(property="comments", type="string", example="Evaluation comments", nullable=true),
 *     @OA\Property(property="recommendations", type="string", example="Recommendations", nullable=true),
 *     @OA\Property(property="status", type="string", enum={"draft", "submitted", "reviewed", "approved"}, example="draft", nullable=true)
 * )
 *
 * @OA\Schema(
 *     schema="ProjectEvaluationCommitteeRequest",
 *     type="object",
 *     required={"project_id", "committee_name", "committee_type", "chairman_id"},
 *     @OA\Property(property="project_id", type="integer", example=1),
 *     @OA\Property(property="committee_name", type="string", example="Technical Evaluation Committee", maxLength=255),
 *     @OA\Property(property="committee_type", type="string", enum={"tender_board", "technical", "financial", "opening"}, example="technical"),
 *     @OA\Property(property="chairman_id", type="integer", example=1),
 *     @OA\Property(property="members", type="array", @OA\Items(
 *         type="object",
 *         @OA\Property(property="user_id", type="integer", example=2),
 *         @OA\Property(property="role", type="string", enum={"chairman", "secretary", "member", "observer"}, example="member")
 *     ), nullable=true),
 *     @OA\Property(property="status", type="string", enum={"active", "dissolved"}, example="active", nullable=true),
 *     @OA\Property(property="formed_at", type="string", format="date", example="2024-01-15", nullable=true),
 *     @OA\Property(property="dissolved_at", type="string", format="date", example="2024-12-31", nullable=true)
 * )
 *
 * @OA\Schema(
 *     schema="LegalReviewRequest",
 *     type="object",
 *     required={"review_type", "reviewed_by"},
 *     @OA\Property(property="project_contract_id", type="integer", example=1, nullable=true),
 *     @OA\Property(property="project_id", type="integer", example=1, nullable=true),
 *     @OA\Property(property="document_id", type="integer", example=1, nullable=true),
 *     @OA\Property(property="review_type", type="string", enum={"contract_review", "compliance_check", "risk_assessment", "variation_review", "termination_review", "other"}, example="contract_review"),
 *     @OA\Property(property="reviewed_by", type="integer", example=1),
 *     @OA\Property(property="review_status", type="string", enum={"pending", "in_review", "approved", "rejected", "conditional"}, example="pending", nullable=true),
 *     @OA\Property(property="review_date", type="string", format="date", example="2024-01-15", nullable=true),
 *     @OA\Property(property="legal_opinion", type="string", example="Legal opinion text", nullable=true),
 *     @OA\Property(property="compliance_score", type="number", example=85.50, nullable=true, minimum=0, maximum=100),
 *     @OA\Property(property="risks_identified", type="array", @OA\Items(type="string"), nullable=true),
 *     @OA\Property(property="recommendations", type="string", example="Recommendations", nullable=true),
 *     @OA\Property(property="requires_revision", type="boolean", example=false, nullable=true),
 *     @OA\Property(property="revision_notes", type="string", example="Revision notes", nullable=true),
 *     @OA\Property(property="approved_by", type="integer", example=1, nullable=true),
 *     @OA\Property(property="approval_date", type="string", format="date", example="2024-01-20", nullable=true),
 *     @OA\Property(property="rejection_reason", type="string", example="Rejection reason", nullable=true)
 * )
 *
 * @OA\Schema(
 *     schema="LegalClearanceRequest",
 *     type="object",
 *     required={"project_contract_id", "clearance_type"},
 *     @OA\Property(property="project_contract_id", type="integer", example=1),
 *     @OA\Property(property="clearance_type", type="string", enum={"pre_award", "pre_signing", "variation", "termination", "other"}, example="pre_award"),
 *     @OA\Property(property="clearance_status", type="string", enum={"pending", "cleared", "rejected", "conditional", "expired"}, example="pending", nullable=true),
 *     @OA\Property(property="cleared_by", type="integer", example=1, nullable=true),
 *     @OA\Property(property="clearance_date", type="string", format="date", example="2024-01-15", nullable=true),
 *     @OA\Property(property="clearance_reference", type="string", example="CLR-2024-001", nullable=true, maxLength=100),
 *     @OA\Property(property="conditions", type="array", @OA\Items(type="string"), nullable=true),
 *     @OA\Property(property="expiry_date", type="string", format="date", example="2025-01-15", nullable=true),
 *     @OA\Property(property="compliance_requirements", type="array", @OA\Items(type="string"), nullable=true),
 *     @OA\Property(property="notes", type="string", example="Clearance notes", nullable=true),
 *     @OA\Property(property="rejection_reason", type="string", example="Rejection reason", nullable=true)
 * )
 *
 * @OA\Schema(
 *     schema="ContractVariationRequest",
 *     type="object",
 *     required={"project_contract_id", "variation_type", "variation_reference", "original_value", "variation_amount", "new_total_value", "reason", "initiated_by", "initiated_date"},
 *     @OA\Property(property="project_contract_id", type="integer", example=1),
 *     @OA\Property(property="variation_type", type="string", enum={"price_adjustment", "scope_change", "time_extension", "specification_change", "termination", "other"}, example="price_adjustment"),
 *     @OA\Property(property="variation_reference", type="string", example="VAR-2024-001", maxLength=100),
 *     @OA\Property(property="original_value", type="number", example=1000000.00, minimum=0),
 *     @OA\Property(property="variation_amount", type="number", example=100000.00),
 *     @OA\Property(property="new_total_value", type="number", example=1100000.00, minimum=0),
 *     @OA\Property(property="reason", type="string", example="Price adjustment due to market changes"),
 *     @OA\Property(property="description", type="string", example="Detailed variation description", nullable=true),
 *     @OA\Property(property="initiated_by", type="integer", example=1),
 *     @OA\Property(property="initiated_date", type="string", format="date", example="2024-01-15"),
 *     @OA\Property(property="legal_review_id", type="integer", example=1, nullable=true),
 *     @OA\Property(property="approval_status", type="string", enum={"pending", "approved", "rejected", "conditional"}, example="pending", nullable=true),
 *     @OA\Property(property="approved_by", type="integer", example=1, nullable=true),
 *     @OA\Property(property="approval_date", type="string", format="date", example="2024-01-20", nullable=true),
 *     @OA\Property(property="approval_notes", type="string", example="Approval notes", nullable=true),
 *     @OA\Property(property="rejection_reason", type="string", example="Rejection reason", nullable=true),
 *     @OA\Property(property="variation_document_url", type="string", example="https://example.com/variation.pdf", nullable=true, maxLength=500)
 * )
 *
 * @OA\Schema(
 *     schema="LegalComplianceCheckRequest",
 *     type="object",
 *     required={"project_contract_id", "compliance_type", "checked_by", "check_date"},
 *     @OA\Property(property="project_contract_id", type="integer", example=1),
 *     @OA\Property(property="compliance_type", type="string", enum={"procurement_act", "fiscal_responsibility", "public_accounts", "company_law", "tax_compliance", "other"}, example="procurement_act"),
 *     @OA\Property(property="check_status", type="string", enum={"pending", "passed", "failed", "conditional"}, example="pending", nullable=true),
 *     @OA\Property(property="checked_by", type="integer", example=1),
 *     @OA\Property(property="check_date", type="string", format="date", example="2024-01-15"),
 *     @OA\Property(property="findings", type="string", example="Compliance findings", nullable=true),
 *     @OA\Property(property="corrective_actions", type="string", example="Required corrective actions", nullable=true),
 *     @OA\Property(property="follow_up_date", type="string", format="date", example="2024-02-15", nullable=true),
 *     @OA\Property(property="compliance_score", type="number", example=90.00, nullable=true, minimum=0, maximum=100),
 *     @OA\Property(property="requires_remediation", type="boolean", example=false, nullable=true),
 *     @OA\Property(property="remediation_plan", type="string", example="Remediation plan details", nullable=true)
 * )
 *
 * @OA\Schema(
 *     schema="LegalDocumentRequest",
 *     type="object",
 *     required={"project_contract_id", "document_type", "document_name", "document_url", "uploaded_by", "uploaded_at"},
 *     @OA\Property(property="project_contract_id", type="integer", example=1),
 *     @OA\Property(property="document_type", type="string", enum={"contract_draft", "signed_contract", "addendum", "legal_opinion", "clearance_certificate", "variation_order", "termination_notice", "other"}, example="contract_draft"),
 *     @OA\Property(property="document_name", type="string", example="Contract Agreement", maxLength=255),
 *     @OA\Property(property="document_url", type="string", example="https://example.com/contract.pdf", maxLength=500),
 *     @OA\Property(property="version", type="integer", example=1, nullable=true, minimum=1),
 *     @OA\Property(property="uploaded_by", type="integer", example=1),
 *     @OA\Property(property="uploaded_at", type="string", format="date", example="2024-01-15"),
 *     @OA\Property(property="is_current", type="boolean", example=true, nullable=true),
 *     @OA\Property(property="requires_signature", type="boolean", example=true, nullable=true),
 *     @OA\Property(property="signed_by", type="array", @OA\Items(type="integer"), nullable=true),
 *     @OA\Property(property="signed_at", type="string", format="date", example="2024-01-20", nullable=true),
 *     @OA\Property(property="description", type="string", example="Document description", nullable=true)
 * )
 *
 * @OA\Schema(
 *     schema="ContractDisputeRequest",
 *     type="object",
 *     required={"project_contract_id", "dispute_type", "dispute_reference", "description", "raised_by", "raised_date"},
 *     @OA\Property(property="project_contract_id", type="integer", example=1),
 *     @OA\Property(property="dispute_type", type="string", enum={"payment", "performance", "variation", "termination", "quality", "delay", "other"}, example="payment"),
 *     @OA\Property(property="dispute_reference", type="string", example="DSP-2024-001", maxLength=100),
 *     @OA\Property(property="description", type="string", example="Dispute description"),
 *     @OA\Property(property="raised_by", type="string", enum={"contractor", "government", "both"}, example="contractor"),
 *     @OA\Property(property="raised_date", type="string", format="date", example="2024-01-15"),
 *     @OA\Property(property="status", type="string", enum={"open", "under_negotiation", "mediation", "arbitration", "litigation", "resolved", "escalated", "closed"}, example="open", nullable=true),
 *     @OA\Property(property="resolution_method", type="string", enum={"negotiation", "mediation", "arbitration", "litigation", "settlement", "other"}, example="negotiation", nullable=true),
 *     @OA\Property(property="resolved_date", type="string", format="date", example="2024-02-15", nullable=true),
 *     @OA\Property(property="resolution_notes", type="string", example="Resolution notes", nullable=true),
 *     @OA\Property(property="disputed_amount", type="number", example=50000.00, nullable=true, minimum=0),
 *     @OA\Property(property="resolved_amount", type="number", example=45000.00, nullable=true, minimum=0),
 *     @OA\Property(property="legal_counsel_id", type="integer", example=1, nullable=true),
 *     @OA\Property(property="legal_advice", type="string", example="Legal advice", nullable=true),
 *     @OA\Property(property="supporting_documents", type="array", @OA\Items(type="string"), nullable=true)
 * )
 */
abstract class Controller
{
    use ApiResponse, ResourceContainer;
}
