<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>{{ session('company_name', config('app.name', 'Invoice System')) }} - @yield('title', 'Dashboard')</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            /* AccountGo Palette */
            --primary-color: #6fd943; /* Bright Green */
            --primary-hover: #5ac72f;
            
            --secondary-color: #3ec9d6; /* Blue */
            --warning-color: #ffa21d; /* Orange */
            --danger-color: #ff3a6e; /* Red */
            
            --text-main: #293240; /* Dark Blue-Gray */
            --text-muted: #6c757d;
            --bg-body: #f8f9fd; /* Very light blue-gray */
            --bg-surface: #ffffff;
            
            --border-color: #edf2f9;
            
            --sidebar-width: 260px;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-body);
            color: var(--text-main);
            -webkit-font-smoothing: antialiased;
        }
        
        /* Navbar */
        .navbar {
            background: var(--bg-surface);
            box-shadow: none;
            border-bottom: 1px solid var(--border-color);
            padding: 1rem 1.5rem;
            z-index: 1000;
        }
        
        .navbar-brand {
            font-weight: 800;
            font-size: 1.5rem;
            color: var(--text-main) !important;
            letter-spacing: -0.02em;
        }
        
        .navbar-brand span {
            color: var(--primary-color);
        }

        /* Sidebar - AccountGo Style (Light) */
        .sidebar {
            background: var(--bg-surface);
            min-height: calc(100vh - 84px);
            border-right: 1px solid var(--border-color);
            padding-top: 2rem;
        }
        
        .sidebar .nav-link {
            color: var(--text-main);
            padding: 12px 20px;
            border-radius: 0 50px 50px 0; /* Rounded right edge */
            margin: 4px 16px 4px 0; /* Attached to left, margin on right */
            font-weight: 500;
            font-size: 0.95rem;
            transition: all 0.2s ease;
            position: relative;
        }
        
        .sidebar .nav-link:hover {
            color: var(--primary-color);
            background-color: rgba(111, 217, 67, 0.1);
        }
        
        .sidebar .nav-link.active {
            background-color: var(--primary-color);
            color: white;
            box-shadow: 0 4px 20px -5px rgba(111, 217, 67, 0.4);
        }
        
        .sidebar .nav-link i {
            margin-right: 15px;
            width: 20px;
            text-align: center;
            font-size: 1.1em;
        }
        
        /* Dashboard Cards & Layout */
        .card {
            background: var(--bg-surface);
            border: none;
            border-radius: 15px;
            box-shadow: 0 0 20px 0 rgba(76, 87, 125, 0.02);
            margin-bottom: 1.5rem;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        
        .card:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 30px -5px rgba(76, 87, 125, 0.1);
        }
        
        .card-header {
            background: transparent;
            border-bottom: 1px solid var(--border-color);
            font-weight: 700;
            color: var(--text-main);
            padding: 1.25rem 1.5rem;
            font-size: 1.1rem;
        }
        
        .main-content {
            padding: 2rem !important;
        }
        
        /* Buttons */
        .btn {
            border-radius: 8px;
            padding: 0.6rem 1.2rem;
            font-weight: 600;
            transition: all 0.2s;
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            color: white;
            box-shadow: 0 4px 20px -5px rgba(111, 217, 67, 0.4);
        }
        
        .btn-primary:hover {
            background-color: var(--primary-hover);
            border-color: var(--primary-hover);
            transform: translateY(-2px);
            box-shadow: 0 6px 25px -5px rgba(111, 217, 67, 0.5);
        }

        /* Animations */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .main-content > * {
            animation: fadeIn 0.4s ease-out forwards;
        }
        }

        /* Restoring & Enhancing Global Styles */
        
        /* Tables - AccountGo Style */
        .table thead th {
            text-transform: uppercase;
            font-size: 0.75rem;
            font-weight: 700;
            color: #8898aa;
            background-color: #f6f9fc;
            border-bottom: 1px solid #e9ecef;
            padding: 1rem 1.5rem;
            letter-spacing: 0.5px;
            white-space: nowrap;
        }
        
        .table tbody td {
            padding: 1rem 1.5rem;
            vertical-align: middle;
            border-bottom: 1px solid #e9ecef;
            font-size: 0.9rem;
            color: #525f7f;
        }
        
        .table-hover tbody tr:hover {
            background-color: #f6f9fc;
        }

        /* Action Buttons */
        .action-btn {
            width: 30px;
            height: 30px;
            padding: 0;
            display: inline-flex;
            justify-content: center;
            align-items: center;
            border-radius: 6px;
            box-shadow: 0 4px 6px rgba(50, 50, 93, .11), 0 1px 3px rgba(0, 0, 0, .08);
            border: none;
            transition: all 0.15s ease;
            color: #fff !important;
        }
        
        .action-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 7px 14px rgba(50, 50, 93, .1), 0 3px 6px rgba(0, 0, 0, .08);
        }
        
        .bg-info   { background-color: #11cdef !important; }
        .bg-danger { background-color: #f5365c !important; }
        .bg-warning { background-color: #fb6340 !important; }
        .bg-primary { background-color: #5e72e4 !important; }
        .bg-success { background-color: #2dce89 !important; }

        /* Card Header Controls */
        .card-header-controls {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid #e9ecef;
            background-color: #fff;
            border-top-left-radius: 0.375rem;
            border-top-right-radius: 0.375rem;
        }
        
        /* Pagination */
        .pagination .page-link {
            border: none;
            border-radius: 50%;
            width: 36px;
            height: 36px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 3px;
            color: #8898aa;
            font-weight: 600;
        }
        
        .pagination .page-item.active .page-link {
            background-color: var(--primary-color);
            color: #fff;
            box-shadow: 0 7px 14px rgba(50, 50, 93, .1), 0 3px 6px rgba(0, 0, 0, .08);
        }
    </style>
        
         
    @stack('styles')
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light sticky-top">
        <div class="container-fluid">
            <a class="navbar-brand" href="{{ Auth::user()->isDriver() ? route('driver.dashboard') : route('dashboard') }}">
                {{ session('company_name', config('app.name', 'Invoice System')) }}
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <!-- Mobile Menu (Visible only on small screens) -->
                <ul class="navbar-nav me-auto d-md-none mb-2">
                        @if(Auth::user()->isDriver())
                        <li class="nav-item">
                            <a class="nav-link text-dark {{ request()->routeIs('driver.dashboard') ? 'active' : '' }}" href="{{ route('driver.dashboard') }}">
                                <i class="bi bi-speedometer2 me-2"></i>Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-dark {{ request()->routeIs('driver.trips.*') ? 'active' : '' }}" href="{{ route('driver.trips.index') }}">
                                <i class="bi bi-truck me-2"></i>My Trips
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-dark {{ request()->routeIs('driver.payments.*') ? 'active' : '' }}" href="{{ route('driver.payments.index') }}">
                                <i class="bi bi-wallet2 me-2"></i>Payments
                            </a>
                        </li>
                        @else
                        <li class="nav-item">
                            <a class="nav-link text-dark {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                                <i class="bi bi-speedometer2 me-2"></i>Dashboard
                            </a>
                        </li>
                    <li class="nav-item">
                        <a class="nav-link text-dark {{ request()->routeIs('companies.*') ? 'active' : '' }}" href="{{ route('companies.index') }}">
                            <i class="bi bi-building me-2"></i>Company
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-dark {{ request()->routeIs('parties.*') ? 'active' : '' }}" href="{{ route('parties.index') }}">
                            <i class="bi bi-people me-2"></i>Parties
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-dark {{ request()->routeIs('challans.*') ? 'active' : '' }}" href="{{ route('challans.index') }}">
                            <i class="bi bi-file-text me-2"></i>Challans
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-dark {{ request()->routeIs('invoices.*') ? 'active' : '' }}" href="{{ route('invoices.index') }}">
                            <i class="bi bi-receipt-cutoff me-2"></i>Invoices
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-dark {{ request()->routeIs('payments.*') ? 'active' : '' }}" href="{{ route('payments.index') }}">
                            <i class="bi bi-cash-stack me-2"></i>Payments
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle text-dark {{ request()->routeIs('admin.drivers.*') || request()->routeIs('admin.trips.*') || request()->routeIs('admin.driver-payments.*') ? 'active' : '' }}" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-truck me-2"></i>Transportation
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item {{ request()->routeIs('admin.drivers.index') ? 'active' : '' }}" href="{{ route('admin.drivers.index') }}">Drivers</a></li>
                            <li><a class="dropdown-item {{ request()->routeIs('admin.trips.index') ? 'active' : '' }}" href="{{ route('admin.trips.index') }}">All Trips</a></li>
                            <li><a class="dropdown-item {{ request()->routeIs('admin.driver-payments.index') ? 'active' : '' }}" href="{{ route('admin.driver-payments.index') }}">Driver Payments</a></li>
                            <li><a class="dropdown-item {{ request()->routeIs('admin.transport.reports.index') ? 'active' : '' }}" href="{{ route('admin.transport.reports.index') }}">Reports</a></li>
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle text-dark {{ request()->routeIs('reports.*') ? 'active' : '' }}" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-graph-up me-2"></i>Reports
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item {{ request()->routeIs('reports.index') ? 'active' : '' }}" href="{{ route('reports.index') }}">Monthly Sales</a></li>
                            <li><a class="dropdown-item {{ request()->routeIs('reports.party-statement') ? 'active' : '' }}" href="{{ route('reports.party-statement') }}">Party Ledger</a></li>
                            <li><a class="dropdown-item {{ request()->routeIs('reports.gst-summary') ? 'active' : '' }}" href="{{ route('reports.gst-summary') }}">GST Report</a></li>
                        </ul>
                    </li>
                    <li><hr class="dropdown-divider text-white border-secondary"></li>
                    @endif
                </ul>

                <ul class="navbar-nav ms-auto">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle text-dark" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle me-1"></i>{{ Auth::user()->name }}
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li>
                                <a class="dropdown-item" href="{{ route('profile.edit') }}">
                                    <i class="bi bi-person me-2"></i>Profile
                                </a>
                            </li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="dropdown-item">
                                        <i class="bi bi-box-arrow-right me-2"></i>Logout
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-2 sidebar d-none d-md-block py-3">
                <nav class="nav flex-column">
                    @if(Auth::user()->isDriver())
                        <a class="nav-link {{ request()->routeIs('driver.dashboard') ? 'active' : '' }}" href="{{ route('driver.dashboard') }}">
                            <i class="bi bi-speedometer2"></i>Dashboard
                        </a>
                        <a class="nav-link {{ request()->routeIs('driver.trips.*') ? 'active' : '' }}" href="{{ route('driver.trips.index') }}">
                            <i class="bi bi-truck"></i>My Trips
                        </a>
                        <a class="nav-link {{ request()->routeIs('driver.payments.*') ? 'active' : '' }}" href="{{ route('driver.payments.index') }}">
                            <i class="bi bi-wallet2"></i>Payments
                        </a>
                    @else
                        <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                            <i class="bi bi-speedometer2"></i>Dashboard
                        </a>
                        <a class="nav-link {{ request()->routeIs('companies.*') ? 'active' : '' }}" href="{{ route('companies.index') }}">
                            <i class="bi bi-building"></i>Company
                        </a>
                        {{-- Staff Dropdown --}}
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.employees.*') || request()->routeIs('admin.upaads.*') || request()->routeIs('admin.salaries.*') ? 'active' : '' }}" href="#staffSubmenu" data-bs-toggle="collapse" aria-expanded="{{ request()->routeIs('admin.employees.*') || request()->routeIs('admin.upaads.*') || request()->routeIs('admin.salaries.*') ? 'true' : 'false' }}">
                                <i class="bi bi-people-fill me-2"></i>Staff <i class="bi bi-chevron-down ms-auto" style="font-size: 0.8em;"></i>
                            </a>
                            <div class="collapse {{ request()->routeIs('admin.employees.*') || request()->routeIs('admin.upaads.*') || request()->routeIs('admin.salaries.*') ? 'show' : '' }}" id="staffSubmenu">
                                <ul class="nav flex-column ms-3">
                                    <li class="nav-item">
                                        <a class="nav-link {{ request()->routeIs('admin.employees.*') ? 'active' : '' }}" href="{{ route('admin.employees.index') }}" style="padding-top: 8px; padding-bottom: 8px;">
                                            Employees
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link {{ request()->routeIs('admin.upaads.*') ? 'active' : '' }}" href="{{ route('admin.upaads.index') }}" style="padding-top: 8px; padding-bottom: 8px;">
                                            Advance (Upaad)
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link {{ request()->routeIs('admin.salaries.*') ? 'active' : '' }}" href="{{ route('admin.salaries.index') }}" style="padding-top: 8px; padding-bottom: 8px;">
                                            Monthly Salaries
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </li>
                        
                        <a class="nav-link {{ request()->routeIs('parties.*') ? 'active' : '' }}" href="{{ route('parties.index') }}">
                            <i class="bi bi-people"></i>Parties
                        </a>
                        <a class="nav-link {{ request()->routeIs('challans.*') ? 'active' : '' }}" href="{{ route('challans.index') }}">
                            <i class="bi bi-file-text"></i>Challans
                        </a>
                        <a class="nav-link {{ request()->routeIs('invoices.*') ? 'active' : '' }}" href="{{ route('invoices.index') }}">
                            <i class="bi bi-receipt-cutoff"></i>Invoices
                        </a>
                        <a class="nav-link {{ request()->routeIs('payments.*') ? 'active' : '' }}" href="{{ route('payments.index') }}">
                            <i class="bi bi-cash-stack"></i>Payments
                        </a>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.drivers.*') || request()->routeIs('admin.trips.*') || request()->routeIs('admin.driver-payments.*') || request()->routeIs('admin.driver-salaries.*') ? 'active' : '' }}" href="#transportationSubmenu" data-bs-toggle="collapse" aria-expanded="{{ request()->routeIs('admin.drivers.*') || request()->routeIs('admin.trips.*') || request()->routeIs('admin.driver-payments.*') || request()->routeIs('admin.driver-salaries.*') ? 'true' : 'false' }}">
                                <i class="bi bi-truck me-2"></i>Transportation <i class="bi bi-chevron-down ms-auto" style="font-size: 0.8em;"></i>
                            </a>
                            <div class="collapse {{ request()->routeIs('admin.drivers.*') || request()->routeIs('admin.trips.*') || request()->routeIs('admin.driver-payments.*') || request()->routeIs('admin.driver-salaries.*') ? 'show' : '' }}" id="transportationSubmenu">
                                <ul class="nav flex-column ps-3">
                                    <li class="nav-item">
                                        <a class="nav-link {{ request()->routeIs('admin.drivers.*') ? 'active' : '' }}" href="{{ route('admin.drivers.index') }}" style="padding-top: 8px; padding-bottom: 8px;">
                                            Drivers
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link {{ request()->routeIs('admin.trips.*') ? 'active' : '' }}" href="{{ route('admin.trips.index') }}" style="padding-top: 8px; padding-bottom: 8px;">
                                            All Trips
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link {{ request()->routeIs('admin.driver-salaries.*') ? 'active' : '' }}" href="{{ route('admin.driver-salaries.index') }}" style="padding-top: 8px; padding-bottom: 8px;">
                                            Driver Monthly Salaries
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link {{ request()->routeIs('admin.driver-payments.*') ? 'active' : '' }}" href="{{ route('admin.driver-payments.index') }}" style="padding-top: 8px; padding-bottom: 8px;">
                                            Ad-hoc Driver Payments
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link {{ request()->routeIs('admin.transport.reports.*') ? 'active' : '' }}" href="{{ route('admin.transport.reports.index') }}" style="padding-top: 8px; padding-bottom: 8px;">
                                            Reports
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('reports.index') || request()->routeIs('reports.party-statement') ? 'active' : '' }}" href="#reportsSubmenu" data-bs-toggle="collapse" aria-expanded="{{ request()->routeIs('reports.*') ? 'true' : 'false' }}">
                                <i class="bi bi-graph-up me-2"></i>Reports <i class="bi bi-chevron-down ms-auto" style="font-size: 0.8em;"></i>
                            </a>
                            <div class="collapse {{ request()->routeIs('reports.*') ? 'show' : '' }}" id="reportsSubmenu">
                                <ul class="nav flex-column ps-3">
                                    <li class="nav-item">
                                        <a class="nav-link {{ request()->routeIs('reports.index') ? 'active' : '' }}" href="{{ route('reports.index') }}" style="padding-top: 8px; padding-bottom: 8px;">
                                            Monthly Sales
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link {{ request()->routeIs('reports.party-statement') ? 'active' : '' }}" href="{{ route('reports.party-statement') }}" style="padding-top: 8px; padding-bottom: 8px;">
                                            Party Ledger
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link {{ request()->routeIs('reports.gst-summary') ? 'active' : '' }}" href="{{ route('reports.gst-summary') }}" style="padding-top: 8px; padding-bottom: 8px;">
                                            GST Report
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link {{ request()->routeIs('reports.salary-report') ? 'active' : '' }}" href="{{ route('reports.salary-report') }}" style="padding-top: 8px; padding-bottom: 8px;">
                                            Salary Report(HR)
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link {{ request()->routeIs('reports.upaad-report') ? 'active' : '' }}" href="{{ route('reports.upaad-report') }}" style="padding-top: 8px; padding-bottom: 8px;">
                                            Upaad Report
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link {{ request()->routeIs('reports.employee-statement') ? 'active' : '' }}" href="{{ route('reports.employee-statement') }}" style="padding-top: 8px; padding-bottom: 8px;">
                                            Employee Statement
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </li>
                    @endif
                </nav>
            </div>
            
            <!-- Main Content -->
            <main class="col-md-10 ms-auto py-4 px-4 main-content">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
                
                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-circle me-2"></i>{{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
                
                @yield('content')
            </main>
        </div>
    </div>
    
    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>
