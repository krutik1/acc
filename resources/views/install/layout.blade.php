<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Installation Wizard - {{ config('app.name', 'Invoice System') }}</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="{{ asset('assets/vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="{{ asset('assets/vendor/bootstrap-icons/css/bootstrap-icons.css') }}" rel="stylesheet">

    
    <style>
        :root {
            --primary-color: #6fd943;
            --primary-hover: #5ac72f;
            --text-main: #293240;
            --bg-body: #f8f9fd;
            --bg-surface: #ffffff;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-body);
            color: var(--text-main);
        }
        
        .wizard-container {
            max-width: 600px;
            margin: 50px auto;
        }

        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 0 20px 0 rgba(76, 87, 125, 0.05);
            background: var(--bg-surface);
        }

        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            color: white;
            font-weight: 600;
        }
        
        .btn-primary:hover {
            background-color: var(--primary-hover);
            border-color: var(--primary-hover);
        }

        .step-indicator {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
            padding: 0 20px;
        }

        .step {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background: #e9ecef;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            color: #6c757d;
        }

        .step.active {
            background: var(--primary-color);
            color: white;
        }
        
        .step.completed {
            background: var(--primary-color);
            color: white;
        }
    </style>
</head>
<body>
    <div class="container wizard-container">
        <div class="text-center mb-4">
            <h3>Installation Wizard</h3>
            <p class="text-muted">Setup your application in a few steps</p>
        </div>

        <div class="card p-4">
            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif
            
            @yield('content')
        </div>
        
        <div class="text-center mt-3 text-muted">
            <small>&copy; {{ date('Y') }} Invoice System</small>
        </div>
    </div>
</body>
</html>
