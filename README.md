# Laravel Invoice System

A production-ready Laravel application for small-business invoice management with Party Management, Delivery Challan, and Invoice modules.

## Tech Stack

- **Framework**: Laravel 11
- **PHP**: 8.2+
- **Database**: MySQL
- **Frontend**: Blade + Bootstrap 5
- **Authentication**: Laravel Breeze
- **PDF**: barryvdh/laravel-dompdf

## Features

### 1. Party Management
- Full CRUD operations for parties
- Auto-fill party details on selection
- Search by name, contact, or GST number

### 2. Delivery Challan
- Party-wise challan creation
- Dynamic add/remove item rows
- Real-time calculation in UI
- Server-side recalculation (secure)
- Auto-generated challan numbers

### 3. Invoice Creation
- Step-based invoice flow
- Select party → Load challans via AJAX → Select challans → Apply GST/TDS/Discount
- Calculations:
  - **Subtotal** = Sum of selected challans
  - **GST** = Subtotal × GST %
  - **TDS** = Subtotal × TDS %
  - **Discount** = Fixed or Percentage
  - **Final Amount** = Subtotal + GST − TDS − Discount

### 4. PDF Generation
- Professional invoice PDF template
- Print-friendly view
- Download and print support

## Installation

### Prerequisites
- PHP 8.2+
- MySQL 8.0+
- Composer
- Node.js & NPM

### Setup Instructions

1. **Clone/Navigate to the project**
   ```bash
   cd /path/to/laravel
   ```

2. **Install PHP dependencies**
   ```bash
   composer install
   ```

3. **Copy environment file**
   ```bash
   cp .env.example .env
   ```

4. **Configure database in `.env`**
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=invoice_system
   DB_USERNAME=your_username
   DB_PASSWORD=your_password
   ```

5. **Generate application key**
   ```bash
   php artisan key:generate
   ```

6. **Run migrations and seeders**
   ```bash
   php artisan migrate:fresh --seed
   ```

7. **Install and build frontend assets**
   ```bash
   npm install
   npm run build
   ```

8. **Start the development server**
   ```bash
   php artisan serve
   ```

9. **Access the application**
   - URL: http://localhost:8000
   - Login: `admin@invoice.com`
   - Password: `password`

## Database Structure

| Table | Description |
|-------|-------------|
| `parties` | Party/customer information |
| `challans` | Delivery challans with subtotal |
| `challan_items` | Individual items in challans |
| `invoices` | Invoice with calculations |
| `invoice_challans` | Pivot table linking invoices to challans |

## Routes

| Route | Description |
|-------|-------------|
| `/dashboard` | Dashboard with stats |
| `/parties` | Party management |
| `/challans` | Challan management |
| `/invoices` | Invoice management |
| `/invoices/{id}/pdf` | Download invoice PDF |
| `/invoices/{id}/print` | Print-friendly view |

## API Endpoints (Internal)

| Endpoint | Description |
|----------|-------------|
| `GET /api/parties/{id}/details` | Get party details (AJAX) |
| `GET /api/parties/{id}/challans` | Get party's uninvoiced challans |
| `POST /api/invoices/calculate` | Calculate invoice amounts |

## Customization

### Company Information
Edit the header in these files:
- `resources/views/invoices/pdf.blade.php`
- `resources/views/invoices/print.blade.php`

Replace "YOUR COMPANY NAME" and address with your actual business details.

## Security Features

- CSRF protection on all forms
- Server-side validation via Form Requests
- Server-side recalculation (never trust frontend)
- Database transactions for multi-table operations
- Cascade deletes for referential integrity

## License

MIT License
