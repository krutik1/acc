# Invoice Management System - Project Description

## Project Overview

This is a comprehensive **Invoice Management System** built with Laravel 12, designed for businesses that need to manage multiple companies/firms, their customers (parties), delivery challans, and tax invoices. The system implements a multi-tenant architecture where each company's data is completely isolated.

## Core Features

### 1. Multi-Company Management (Multi-Tenant System)
- **Company Selection**: Users must select a company/firm before accessing the system
- **Company Isolation**: All data (parties, challans, invoices) is filtered by the selected company
- **Company CRUD**: Full Create, Read, Update, Delete operations for companies
- **Company Profile**: Each company can store:
  - Name, Address
  - GST Number, State Code
  - Mobile Numbers (comma-separated)
  - Bank Details (Bank Name, IFSC Code, Account Number)
  - Terms and Conditions
  - Default company flag

### 2. Party (Customer) Management
- **Full CRUD Operations** for parties
- **Party Information**:
  - Name, Address
  - Contact Number
  - GST Number
- **Search Functionality**: Search parties by name, contact number, or GST number
- **Company-Scoped**: All parties are associated with the selected company

### 3. Challan (Delivery Challan) Management
- **Full CRUD Operations** for challans
- **Challan Features**:
  - Auto-generated or manually entered Challan Numbers
  - AJAX duplicate validation for challan numbers
  - Challan Date
  - Multiple items per challan
  - Item details: Description, Quantity, Unit, Rate, Amount
  - Automatic subtotal calculation
  - **Editable even after invoicing** (allows corrections and re-invoicing)
- **Challan Status**: 
  - Pending (not invoiced)
  - Invoiced (already included in an invoice)
- **Company-Scoped**: All challans belong to the selected company

### 4. Invoice Management
- **Full CRUD Operations** for invoices
- **Invoice Creation**:
  - Select multiple challans from a party
  - Automatic invoice number generation (year-based)
  - Invoice date
  - Apply discounts (Fixed amount or Percentage)
  - Apply GST percentage (calculated on discounted amount)
  - Apply TDS percentage (calculated on discounted amount)
  - Automatic calculation of all amounts
- **Invoice Calculation Logic**:
  1. Calculate discount first (fixed or percentage)
  2. Apply discount to get discounted subtotal
  3. Calculate GST on the discounted amount (not original)
  4. Calculate TDS on the discounted amount
  5. Final Amount = Discounted Subtotal + GST - TDS
- **Invoice Editing**:
  - Can add or remove challans from existing invoices
  - Can update all invoice details (discounts, GST, TDS, dates)
  - Automatic cleanup: If all challans are removed from an invoice, old invoice is deleted
  - If challans are re-invoiced, old invoices are automatically deleted
- **Invoice PDF Generation**:
  - Professional TAX INVOICE format
  - Dynamic company header
  - Itemized challan details
  - Tax calculations (CGST/SGST split)
  - Amount in words conversion
  - Bank details
  - Terms and conditions
- **Invoice Printing**: Browser-based print view matching PDF format
- **Company-Scoped**: All invoices belong to the selected company

### 5. Dashboard
- **Statistics Cards**:
  - Total Parties
  - Total Challans
  - Pending Challans
  - Total Invoices
  - Total Invoiced Amount
- **Recent Activity**:
  - Recent Challans (latest 5)
  - Recent Invoices (latest 5)
- **Quick Actions**:
  - Add New Party
  - Create Challan
  - Create Invoice
- **Company Display**: Shows currently selected company name
- **Company Switching**: Quick access to switch between companies

## Technical Architecture

### Technology Stack
- **Backend**: Laravel 12 (PHP 8.3)
- **Frontend**: Bootstrap 5, Bootstrap Icons
- **Database**: MySQL 8.4
- **PDF Generation**: Barryvdh\DomPDF
- **Authentication**: Laravel Breeze (default authentication)

### Database Structure

#### Companies Table
- id, name, address
- contact_number, gst_number, state_code
- mobile_numbers (comma-separated)
- bank_name, ifsc_code, account_number
- terms_conditions (text)
- is_default (boolean)
- timestamps

#### Parties Table
- id, company_id (foreign key)
- name, address
- contact_number, gst_number
- timestamps

#### Challans Table
- id, company_id (foreign key), party_id (foreign key)
- challan_number (unique), challan_date
- subtotal (decimal)
- is_invoiced (boolean)
- timestamps

#### Challan Items Table
- id, challan_id (foreign key)
- description, quantity, unit, rate, amount
- timestamps

#### Invoices Table
- id, company_id (foreign key), party_id (foreign key)
- invoice_number (unique), invoice_date
- subtotal, gst_percent, gst_amount
- tds_percent, tds_amount
- discount_type (enum: fixed/percentage)
- discount_value, discount_amount
- final_amount, notes
- timestamps

#### Invoice Challans Pivot Table
- invoice_id, challan_id
- timestamps

### Key Relationships
- Company → hasMany → Parties
- Company → hasMany → Challans
- Company → hasMany → Invoices
- Party → belongsTo → Company
- Party → hasMany → Challans
- Party → hasMany → Invoices
- Challan → belongsTo → Company
- Challan → belongsTo → Party
- Challan → hasMany → ChallanItems
- Challan → belongsToMany → Invoices
- Invoice → belongsTo → Company
- Invoice → belongsTo → Party
- Invoice → belongsToMany → Challans

### Middleware & Security
- **Authentication Middleware**: All routes require authentication
- **SetCompany Middleware**: Ensures company is selected before accessing main features
- **Company Isolation**: All queries are scoped to the selected company
- **CSRF Protection**: All forms are protected with CSRF tokens

## Business Logic

### Challan Number Generation
- Format: `CHYYYYNNNNNN` (e.g., CH2024000001)
- Auto-generated if not provided
- Validated for uniqueness within the company
- Year-based sequential numbering

### Invoice Number Generation
- Format: `INVYYYYNNNNNN` (e.g., INV2024000001)
- Auto-generated
- Year-based sequential numbering per company

### Invoice Calculation Flow
1. **Subtotal**: Sum of all selected challans' subtotals
2. **Discount Calculation**:
   - If percentage: `discount_amount = subtotal × (discount_value / 100)`
   - If fixed: `discount_amount = min(discount_value, subtotal)`
3. **Discounted Subtotal**: `subtotal - discount_amount`
4. **GST Calculation**: `gst_amount = discounted_subtotal × (gst_percent / 100)`
5. **TDS Calculation**: `tds_amount = discounted_subtotal × (tds_percent / 100)`
6. **Final Amount**: `discounted_subtotal + gst_amount - tds_amount`

### Re-invoicing Logic
- When a challan is re-invoiced:
  - If the old invoice contains only that challan → Delete the entire old invoice
  - If the old invoice contains multiple challans → Detach only that challan
  - Mark the challan as invoiced in the new invoice
- When an invoice is edited:
  - Added challans are marked as invoiced
  - Removed challans are marked as not invoiced (if not in other invoices)
  - Old invoices are cleaned up automatically

## User Workflow

### Initial Setup
1. User logs in
2. System redirects to company selection page
3. User creates a company (if none exists) or selects existing company
4. User is redirected to dashboard

### Creating an Invoice
1. Go to Parties → Create/Select a Party
2. Go to Challans → Create Challan(s) for the party
   - Add items with quantities, rates
   - System calculates amounts automatically
3. Go to Invoices → Create Invoice
   - Select the party
   - Select one or more challans
   - System shows subtotal
   - Enter discount (optional)
   - Enter GST percentage (optional)
   - Enter TDS percentage (optional)
   - System calculates final amount automatically
   - Save invoice
4. View/Download/Print Invoice
   - View invoice details
   - Download as PDF
   - Print from browser

### Switching Companies
1. Click "Switch Company" on dashboard
2. Select different company from list
3. All data is now filtered for the new company

## Invoice PDF Format

The invoice PDF follows a professional TAX INVOICE format:

### Header
- **Title**: "TAX INVOICE" (centered, underlined)
- **Left Side**: Company name, address, GSTI No, State Code
- **Right Side**: Mobile numbers

### Invoice Details
- **Left Side**: Customer details (M/s.: Party name, address, GSTI No, State Code)
- **Right Side**: Invoice number, Date, Due Day, Due Date

### Items Table
Columns: Ch. No | Ch. Date | PARTICULARS | HSN | Taka | Mtrs | RATE | AMOUNT
- Lists all items from selected challans
- Shows totals at the bottom

### Summary Section
- CGST (50% of total GST) with percentage and amount
- SGST (50% of total GST) with percentage and amount
- Rounding amount
- Total Amount (bold)

### Additional Sections
- **Amount in Words**: Converted total amount (e.g., "Rupees Fifty-Seven Thousand Seven Hundred Twenty-Four Only")
- **Bank Details**: Bank Name, IFSC Code, Account Number
- **Terms and Conditions**: Numbered list of terms
- **Signature**: "FOR [COMPANY NAME]"

## API Endpoints

### AJAX Endpoints
- `GET /api/parties/{party}/details` - Get party details
- `GET /api/parties/{party}/challans` - Get challans for a party
- `POST /api/challans/check-duplicate` - Validate duplicate challan number
- `POST /api/invoices/calculate` - Calculate invoice amounts

## Key Files & Structure

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── ChallanController.php
│   │   ├── CompanyController.php
│   │   ├── CompanySelectionController.php
│   │   ├── InvoiceController.php
│   │   └── PartyController.php
│   ├── Middleware/
│   │   └── SetCompany.php
│   └── Requests/
│       ├── ChallanRequest.php
│       └── InvoiceRequest.php
├── Models/
│   ├── Challan.php
│   ├── ChallanItem.php
│   ├── Company.php
│   ├── Invoice.php
│   └── Party.php
database/
└── migrations/
    ├── create_companies_table.php
    ├── create_parties_table.php
    ├── create_challans_table.php
    ├── create_challan_items_table.php
    ├── create_invoices_table.php
    ├── create_invoice_challans_table.php
    └── add_company_id_to_tables.php
resources/
└── views/
    ├── layouts/
    │   └── main.blade.php
    ├── companies/
    │   ├── index.blade.php
    │   ├── create.blade.php
    │   ├── edit.blade.php
    │   ├── show.blade.php
    │   └── select.blade.php
    ├── parties/
    │   ├── index.blade.php
    │   ├── create.blade.php
    │   ├── edit.blade.php
    │   └── show.blade.php
    ├── challans/
    │   ├── index.blade.php
    │   ├── create.blade.php
    │   ├── edit.blade.php
    │   └── show.blade.php
    ├── invoices/
    │   ├── index.blade.php
    │   ├── create.blade.php
    │   ├── edit.blade.php
    │   ├── show.blade.php
    │   ├── pdf.blade.php
    │   └── print.blade.php
    └── dashboard.blade.php
routes/
└── web.php
```

## Future Enhancement Possibilities

1. **Reporting & Analytics**:
   - Sales reports by date range
   - Party-wise sales reports
   - Tax reports (GST, TDS)
   - Profit/loss analysis

2. **Payment Tracking**:
   - Payment records
   - Outstanding invoices
   - Payment reminders

3. **Inventory Management**:
   - Stock tracking
   - Item master
   - Low stock alerts

4. **User Management**:
   - Role-based access control
   - Multiple users per company
   - User permissions

5. **Email Integration**:
   - Send invoices via email
   - Automated reminders
   - Email templates

6. **Export Features**:
   - Excel export for reports
   - Bulk data export
   - Backup/restore

7. **Mobile App**:
   - Mobile-responsive design enhancements
   - PWA support
   - Mobile app for field operations

## Installation & Setup

1. **Prerequisites**:
   - PHP 8.3+
   - MySQL 8.0+
   - Composer
   - Node.js & NPM (for assets)

2. **Installation Steps**:
   ```bash
   composer install
   cp .env.example .env
   php artisan key:generate
   php artisan migrate
   php artisan serve
   ```

3. **Initial Setup**:
   - Register a user account
   - Create your first company
   - Start adding parties, challans, and invoices

## Support & Maintenance

- **Version Control**: Git repository with commit history
- **Code Quality**: Laravel best practices
- **Security**: CSRF protection, authentication, SQL injection prevention
- **Scalability**: Efficient database queries, indexed columns
- **Maintainability**: Clean code structure, separation of concerns

---

**Project Status**: Production Ready
**Last Updated**: 2024
**Framework Version**: Laravel 12.44.0
**PHP Version**: 8.3.16
