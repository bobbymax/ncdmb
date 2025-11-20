# 📦 Inventory & Legal Cycle Module Schema Implementation

**Date**: November 18, 2025  
**Status**: ✅ **Migrations Complete**

---

## 📊 Overview

This document outlines the complete database schema for the **Inventory Module** enhancements and the new **Legal Cycle Module** designed for Nigerian government business structure compliance.

---

## 🗂️ Inventory Module Enhancements

### **New Tables Created (6 tables)**

#### 1. **inventory_receipts**
Tracks goods received from suppliers/vendors.

**Key Fields:**
- `store_supply_id` - Links to store supplies
- `received_by` - User who received the goods
- `location_id` - Where goods were received
- `reference` - Unique receipt reference
- `status` - pending, partial, complete
- `received_at` - Timestamp of receipt

**Migration**: `2025_11_18_100000_create_inventory_receipts_table.php`

#### 2. **inventory_receipt_items**
Line items for each receipt.

**Key Fields:**
- `inventory_receipt_id` - Parent receipt
- `product_id` - Product received
- `batch_id` - Batch/lot tracking
- `quantity_received` - Amount received
- `unit_cost` - Cost per unit

**Migration**: `2025_11_18_100001_create_inventory_receipt_items_table.php`

#### 3. **inventory_transfers**
Tracks transfers between locations.

**Key Fields:**
- `from_location_id` - Source location
- `to_location_id` - Destination location
- `transferred_by` - User who initiated transfer
- `received_by` - User who received (if different)
- `status` - pending, in-transit, completed, cancelled
- `reference` - Unique transfer reference

**Migration**: `2025_11_18_100002_create_inventory_transfers_table.php`

#### 4. **inventory_transfer_items**
Line items for transfers.

**Key Fields:**
- `inventory_transfer_id` - Parent transfer
- `product_id` - Product being transferred
- `quantity_transferred` - Amount sent
- `quantity_received` - Amount received (may differ)

**Migration**: `2025_11_18_100003_create_inventory_transfer_items_table.php`

#### 5. **inventory_reservations**
Reserves stock for requisitions.

**Key Fields:**
- `requisition_item_id` - Links to requisition
- `product_id` - Reserved product
- `location_id` - Location of reserved stock
- `quantity_reserved` - Amount reserved
- `reserved_until` - Expiry of reservation
- `status` - active, fulfilled, cancelled, expired

**Migration**: `2025_11_18_100004_create_inventory_reservations_table.php`

#### 6. **inventory_valuations**
Tracks inventory valuations for accounting.

**Key Fields:**
- `product_id` - Product being valued
- `location_id` - Location of inventory
- `valuation_method` - fifo, lifo, weighted_average, specific_identification
- `unit_cost` - Calculated unit cost
- `quantity_on_hand` - Stock quantity
- `total_value` - Total inventory value
- `valued_at` - Valuation timestamp
- `valued_by` - User who performed valuation

**Migration**: `2025_11_18_100005_create_inventory_valuations_table.php`

---

## ⚖️ Legal Cycle Module

### **New Tables Created (7 tables)**

#### 1. **legal_reviews**
Comprehensive legal review of contracts, projects, or documents.

**Key Fields:**
- `project_contract_id` - Contract under review (nullable)
- `project_id` - Project under review (nullable)
- `document_id` - Document under review (nullable)
- `review_type` - contract_review, compliance_check, risk_assessment, etc.
- `reviewed_by` - Legal officer performing review
- `review_status` - pending, in_review, approved, rejected, conditional
- `legal_opinion` - Legal opinion text
- `compliance_score` - Score from 0-100
- `risks_identified` - JSON array of risks
- `recommendations` - Recommendations text
- `requires_revision` - Boolean flag
- `approved_by` - Head of legal (if required)

**Migration**: `2025_11_18_100010_create_legal_reviews_table.php`

#### 2. **legal_clearances**
Tracks legal clearances required before contract signing/award.

**Key Fields:**
- `project_contract_id` - Contract requiring clearance
- `clearance_type` - pre_award, pre_signing, variation, termination
- `clearance_status` - pending, cleared, rejected, conditional, expired
- `cleared_by` - Legal officer who granted clearance
- `clearance_date` - Date clearance was granted
- `clearance_reference` - Unique reference number
- `conditions` - JSON array of conditions (if conditional)
- `expiry_date` - If conditional clearance
- `compliance_requirements` - JSON array of requirements

**Migration**: `2025_11_18_100011_create_legal_clearances_table.php`

#### 3. **contract_variations**
Tracks contract variations/amendments.

**Key Fields:**
- `project_contract_id` - Parent contract
- `variation_type` - price_adjustment, scope_change, time_extension, etc.
- `variation_reference` - Unique variation reference
- `original_value` - Original contract value
- `variation_amount` - Change amount
- `new_total_value` - New total value
- `reason` - Reason for variation
- `initiated_by` - User who initiated
- `legal_review_id` - Link to legal review (if required)
- `approval_status` - pending, approved, rejected, conditional
- `variation_document_url` - Link to variation document

**Migration**: `2025_11_18_100012_create_contract_variations_table.php`

#### 4. **legal_compliance_checks**
Tracks compliance with various legal frameworks.

**Key Fields:**
- `project_contract_id` - Contract being checked
- `compliance_type` - procurement_act, fiscal_responsibility, public_accounts, etc.
- `check_status` - pending, passed, failed, conditional
- `checked_by` - User performing check
- `check_date` - Date of check
- `findings` - Findings text
- `corrective_actions` - Required actions
- `follow_up_date` - Date for follow-up
- `compliance_score` - Score from 0-100
- `requires_remediation` - Boolean flag

**Migration**: `2025_11_18_100013_create_legal_compliance_checks_table.php`

#### 5. **legal_documents**
Manages legal documents related to contracts.

**Key Fields:**
- `project_contract_id` - Parent contract
- `document_type` - contract_draft, signed_contract, addendum, legal_opinion, etc.
- `document_name` - Name of document
- `document_url` - URL to document file
- `version` - Document version number
- `uploaded_by` - User who uploaded
- `is_current` - Boolean flag for current version
- `requires_signature` - Boolean flag
- `signed_by` - JSON array of signatory IDs
- `signed_at` - Timestamp of signing

**Migration**: `2025_11_18_100014_create_legal_documents_table.php`

#### 6. **contract_disputes**
Tracks contract disputes and resolutions.

**Key Fields:**
- `project_contract_id` - Contract in dispute
- `dispute_type` - payment, performance, variation, termination, etc.
- `dispute_reference` - Unique dispute reference
- `description` - Dispute description
- `raised_by` - contractor, government, both
- `raised_date` - Date dispute was raised
- `status` - open, under_negotiation, mediation, arbitration, resolved, etc.
- `resolution_method` - negotiation, mediation, arbitration, litigation
- `resolved_date` - Date resolved
- `disputed_amount` - Amount in dispute
- `resolved_amount` - Final resolved amount
- `legal_counsel_id` - Assigned legal counsel

**Migration**: `2025_11_18_100015_create_contract_disputes_table.php`

#### 7. **legal_audit_trails**
Complete audit trail for all legal actions.

**Key Fields:**
- `project_contract_id` - Contract (nullable)
- `project_id` - Project (nullable)
- `action_type` - review_created, clearance_granted, variation_approved, etc.
- `performed_by` - User who performed action
- `performed_at` - Timestamp
- `before_values` - JSON of values before change
- `after_values` - JSON of values after change
- `ip_address` - IP address
- `user_agent` - User agent string
- `notes` - Additional notes

**Migration**: `2025_11_18_100016_create_legal_audit_trails_table.php`

---

## 🔗 Enhanced Project Contracts Table

### **New Legal Fields Added**

**Migration**: `2025_11_18_100020_add_legal_fields_to_project_contracts_table.php`

**Fields Added:**
- `legal_review_required` - Boolean (default: true)
- `legal_review_status` - Enum: pending, in_review, cleared, rejected, not_required
- `legal_clearance_obtained` - Boolean (default: false)
- `legal_clearance_date` - Date
- `legal_clearance_reference` - String (100 chars)
- `contract_variations_count` - Integer (default: 0)
- `has_active_disputes` - Boolean (default: false)
- `legal_risk_level` - Enum: low, medium, high, critical
- `last_legal_review_date` - Date

**Indexes Added:**
- `legal_review_status`
- `legal_clearance_obtained`
- `has_active_disputes`
- `legal_risk_level`

---

## 📋 Database Relationships

### **ProjectContract Relationships**
```
ProjectContract
├── hasMany LegalReview
├── hasMany LegalClearance
├── hasMany ContractVariation
├── hasMany LegalComplianceCheck
├── hasMany LegalDocument
├── hasMany ContractDispute
└── hasMany LegalAuditTrail
```

### **Project Relationships**
```
Project
├── hasMany LegalReview (can review project before contract)
└── hasMany LegalAuditTrail
```

### **Inventory Relationships**
```
InventoryReceipt → StoreSupply
InventoryTransfer → InventoryLocation (from/to)
InventoryReservation → RequisitionItem
InventoryValuation → Product, InventoryLocation
```

---

## 🇳🇬 Nigerian Government Compliance

### **Legal Cycle Requirements**
✅ Pre-award legal review (mandatory for contracts >₦50M)  
✅ Pre-signing legal clearance  
✅ Compliance with Public Procurement Act 2007  
✅ Fiscal Responsibility Act compliance  
✅ Variation approval process  
✅ Dispute resolution tracking  
✅ Complete audit trail  

### **Inventory Requirements**
✅ Government asset tagging  
✅ Audit trail for all movements  
✅ Valuation for financial reporting  
✅ Location hierarchy (HQ → Regional → Site)  
✅ Batch tracking for accountability  
✅ Transfer tracking between locations  

---

## 🚀 Migration Execution

Run all migrations in order:

```bash
# Inventory Module
php artisan migrate --path=database/migrations/2025_11_18_100000_create_inventory_receipts_table.php
php artisan migrate --path=database/migrations/2025_11_18_100001_create_inventory_receipt_items_table.php
php artisan migrate --path=database/migrations/2025_11_18_100002_create_inventory_transfers_table.php
php artisan migrate --path=database/migrations/2025_11_18_100003_create_inventory_transfer_items_table.php
php artisan migrate --path=database/migrations/2025_11_18_100004_create_inventory_reservations_table.php
php artisan migrate --path=database/migrations/2025_11_18_100005_create_inventory_valuations_table.php

# Legal Cycle Module
php artisan migrate --path=database/migrations/2025_11_18_100010_create_legal_reviews_table.php
php artisan migrate --path=database/migrations/2025_11_18_100011_create_legal_clearances_table.php
php artisan migrate --path=database/migrations/2025_11_18_100012_create_contract_variations_table.php
php artisan migrate --path=database/migrations/2025_11_18_100013_create_legal_compliance_checks_table.php
php artisan migrate --path=database/migrations/2025_11_18_100014_create_legal_documents_table.php
php artisan migrate --path=database/migrations/2025_11_18_100015_create_contract_disputes_table.php
php artisan migrate --path=database/migrations/2025_11_18_100016_create_legal_audit_trails_table.php

# Project Contracts Enhancement
php artisan migrate --path=database/migrations/2025_11_18_100020_add_legal_fields_to_project_contracts_table.php
```

Or run all at once:
```bash
php artisan migrate
```

---

## 📊 Summary

**Total Migrations Created**: 14  
**Inventory Tables**: 6  
**Legal Cycle Tables**: 7  
**Enhanced Tables**: 1 (project_contracts)

**Status**: ✅ Ready for migration execution

---

## 🔄 Next Steps

1. ✅ Run migrations
2. Generate models using `php artisan pack:generate` for each resource
3. Create relationships in models
4. Implement services and repositories
5. Create API endpoints
6. Build frontend components

---

**Implementation Date**: November 18, 2025  
**Schema Version**: 1.0

