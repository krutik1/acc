<?php

use App\Http\Controllers\ChallanController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\CompanySelectionController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\PartyController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SuggestionController;
use App\Http\Controllers\AdminDriverPaymentController;
use App\Http\Controllers\DriverPaymentController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    if (auth()->check()) {
        if (auth()->user()->hasRole('driver')) {
            return redirect()->route('driver.dashboard');
        }
        if (session('selected_company_id')) {
            return redirect()->route('dashboard');
        }
        return redirect()->route('companies.select');
    }
    return redirect()->route('login');
});

Route::middleware('auth')->group(function () {
    // Company Selection Routes (before company middleware)
    Route::get('/companies/select', [CompanySelectionController::class, 'select'])->name('companies.select');
    Route::post('/companies/select', [CompanySelectionController::class, 'store'])->name('companies.select.store');
    Route::get('/companies/switch', [CompanySelectionController::class, 'switch'])->name('companies.switch');

    // Company Routes (can be accessed without company selection)
    Route::middleware('role:admin')->resource('companies', CompanyController::class);

    // Profile Routes
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Driver Routes
Route::middleware(['auth', 'role:driver'])->prefix('driver')->name('driver.')->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\DriverController::class, 'dashboard'])->name('dashboard');
    Route::get('/reports', [\App\Http\Controllers\DriverReportController::class, 'index'])->name('reports.index');
    Route::get('/payments', [DriverPaymentController::class, 'index'])->name('payments.index');
    Route::get('/monthly-payments', [\App\Http\Controllers\DriverSalaryController::class, 'index'])->name('salaries.index');
    
    // Read-only access for blocked drivers (Index/Show)
    Route::get('trips', [\App\Http\Controllers\TripController::class, 'index'])->name('trips.index');
    // Write access protected by blocked check
    Route::middleware('blocked.check')->group(function () {
        Route::get('trips/create', [\App\Http\Controllers\TripController::class, 'create'])->name('trips.create');
        Route::post('trips', [\App\Http\Controllers\TripController::class, 'store'])->name('trips.store');
        Route::get('trips/{trip}/edit', [\App\Http\Controllers\TripController::class, 'edit'])->name('trips.edit');
        Route::put('trips/{trip}', [\App\Http\Controllers\TripController::class, 'update'])->name('trips.update');
        Route::delete('trips/{trip}', [\App\Http\Controllers\TripController::class, 'destroy'])->name('trips.destroy');
    });

    // Read-only access (Show) - Must be after 'trips/create' to avoid wildcard shadowing
    Route::get('trips/{trip}', [\App\Http\Controllers\TripController::class, 'show'])->name('trips.show');
});

// Admin Routes (Existing Company Middleware)
Route::middleware(['auth', 'company', 'role:admin'])->group(function () {
    Route::get('/dashboard', function () {
        $companyId = session('selected_company_id');
        $stats = [
            'parties' => \App\Models\Party::where('company_id', $companyId)->count(),
            'challans' => \App\Models\Challan::where('company_id', $companyId)->count(),
            'pending_challans' => \App\Models\Challan::where('company_id', $companyId)->where('is_invoiced', false)->count(),
            'invoices' => \App\Models\Invoice::where('company_id', $companyId)->count(),
            'total_invoiced' => \App\Models\Invoice::where('company_id', $companyId)->sum('final_amount'),
            'total_income' => \App\Models\Payment::where('company_id', $companyId)->where('type', 'received')->sum('amount'),
            'total_expense' => \App\Models\Payment::where('company_id', $companyId)->where('type', 'sent')->sum('amount'),
            'pending_trips' => \App\Models\Trip::where('company_id', $companyId)->where('status', 'pending')->count(),
        ];
        return view('dashboard', compact('stats'));
    })->name('dashboard');

    // Party Routes
    Route::resource('parties', PartyController::class);
    Route::get('/api/parties/search', [PartyController::class, 'search'])
        ->name('api.parties.search');
    Route::get('/api/parties/{party}/details', [PartyController::class, 'getDetails'])
        ->name('api.parties.details');
    Route::post('/api/parties/fetch-gst-details', [PartyController::class, 'fetchGstDetails'])
        ->name('api.parties.fetch-gst');

    // Challan Routes
    Route::resource('challans', ChallanController::class);
    Route::get('/api/parties/{party}/challans', [ChallanController::class, 'getByParty'])
        ->name('api.parties.challans');
    Route::post('/api/challans/check-duplicate', [ChallanController::class, 'checkDuplicate'])
        ->name('api.challans.check-duplicate');

    // Invoice Routes
    Route::resource('invoices', InvoiceController::class);
    Route::get('/invoices/{invoice}/pdf', [InvoiceController::class, 'downloadPdf'])
        ->name('invoices.pdf');
    Route::get('/invoices/{invoice}/print', [InvoiceController::class, 'print'])
        ->name('invoices.print');
    Route::post('/api/invoices/calculate', [InvoiceController::class, 'calculate'])
        ->name('api.invoices.calculate');

    // Payment Routes
    Route::resource('payments', PaymentController::class);
    Route::get('/payments/{payment}/print', [PaymentController::class, 'print'])
        ->name('payments.print');

    // Report Routes
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/party-statement', [ReportController::class, 'partyStatement'])->name('reports.party-statement');
    Route::get('/reports/gst-summary', [ReportController::class, 'gstSummary'])->name('reports.gst-summary');
    Route::get('/reports/salary-report', [ReportController::class, 'salaryReport'])->name('reports.salary-report');
    Route::get('/reports/upaad-report', [ReportController::class, 'upaadReport'])->name('reports.upaad-report');
    Route::get('/reports/employee-statement', [ReportController::class, 'employeeStatement'])->name('reports.employee-statement');

    // Suggestions Route
    Route::get('/api/suggestions', [SuggestionController::class, 'search'])->name('api.suggestions');

    // Admin Transportation Routes
    Route::prefix('admin')->name('admin.')->group(function () {
        // Employees
        Route::resource('employees', \App\Http\Controllers\EmployeeController::class);
        
        // Upaads
        Route::resource('upaads', \App\Http\Controllers\UpaadController::class);

        // Salaries
        Route::post('salaries/calculate', [\App\Http\Controllers\SalaryController::class, 'calculate'])->name('salaries.calculate');
        Route::patch('salaries/{salary}/mark-paid', [\App\Http\Controllers\SalaryController::class, 'markPaid'])->name('salaries.markPaid');
        Route::resource('salaries', \App\Http\Controllers\SalaryController::class)->except(['edit', 'update']); // Edit usually not allowed for generated salary, better delete & regenerate

        Route::resource('drivers', \App\Http\Controllers\AdminDriverController::class);
        Route::patch('drivers/{driver}/toggle-block', [\App\Http\Controllers\AdminDriverController::class, 'toggleBlock'])->name('drivers.toggle-block');
        
        Route::resource('trips', \App\Http\Controllers\AdminTripController::class);
        Route::patch('trips/{trip}/status', [\App\Http\Controllers\AdminTripController::class, 'updateStatus'])->name('trips.status');

        // Transportation Reports
        Route::get('transport/reports', [\App\Http\Controllers\Admin\TransportReportController::class, 'index'])->name('transport.reports.index');
        
        // Driver Payments
        Route::resource('driver-payments', AdminDriverPaymentController::class);
        
        // Driver Monthly Salaries
        Route::post('driver-salaries/calculate', [\App\Http\Controllers\AdminDriverSalaryController::class, 'calculate'])->name('driver-salaries.calculate');
        Route::post('driver-salaries/{salary}/mark-paid', [\App\Http\Controllers\AdminDriverSalaryController::class, 'markPaid'])->name('driver-salaries.markPaid');
        Route::resource('driver-salaries', \App\Http\Controllers\AdminDriverSalaryController::class)->except(['edit', 'update', 'show']);
    });
});

require __DIR__.'/auth.php';
