<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dashboard') - Family Document Expiry Tracker</title>
    
    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <style>
        :root {
            --app-primary: #1e3a8a;
            --app-primary-hover: #172554;
            --app-bg: #f8fafc;
        }
        body {
            background-color: var(--app-bg);
            font-family: system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        .navbar-brand {
            font-weight: 700;
            letter-spacing: -0.5px;
            color: var(--app-primary) !important;
        }
        .main-content {
            flex: 1;
        }
        .card {
            border: 1px solid #e2e8f0;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05);
            border-radius: 0.75rem;
        }
        .stat-card {
            transition: transform 0.15s ease, box-shadow 0.15s ease;
        }
        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
        .badge-status {
            font-size: 0.825rem;
            font-weight: 600;
            padding: 0.4em 0.75em;
        }
        .table-hover tbody tr:hover {
            background-color: #f1f5f9;
        }
        .btn-primary {
            background-color: #2563eb;
            border-color: #2563eb;
        }
        .btn-primary:hover {
            background-color: #1d4ed8;
            border-color: #1d4ed8;
        }
    </style>
    @yield('styles')
</head>
<body>
    <!-- Top Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-white bg-white border-bottom sticky-top py-2">
        <div class="container-fluid px-lg-4">
            <a class="navbar-brand d-flex align-items-center gap-2" href="{{ auth()->check() ? route('dashboard') : route('home') }}">
                <i class="bi bi-shield-check text-primary fs-3"></i>
                <span>FamilyDocs<span class="text-secondary fw-normal fs-6 d-none d-sm-inline"> Tracker</span></span>
            </a>

            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain" aria-controls="navbarMain" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarMain">
                @auth
                    <!-- Active Family Workspace Switcher -->
                    @if(isset($currentFamily))
                    <div class="navbar-nav me-auto my-2 my-lg-0 align-items-lg-center">
                        <div class="dropdown me-lg-3">
                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle d-flex align-items-center gap-2" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-house-door-fill text-primary"></i>
                                <strong>{{ $currentFamily->name }}</strong>
                                <span class="badge bg-light text-dark border">{{ ucfirst($currentUserRole ?? 'Member') }}</span>
                            </button>
                            <ul class="dropdown-menu shadow-sm">
                                <li><h6 class="dropdown-header">Switch Workspace</h6></li>
                                @if(isset($userFamilies))
                                    @foreach($userFamilies as $fam)
                                        <li>
                                            <form action="{{ route('family.switch', $fam) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="dropdown-item d-flex align-items-center justify-content-between {{ $fam->id === $currentFamily->id ? 'active' : '' }}">
                                                    <span>{{ $fam->name }}</span>
                                                    @if($fam->id === $currentFamily->id)
                                                        <i class="bi bi-check2"></i>
                                                    @endif
                                                </button>
                                            </form>
                                        </li>
                                    @endforeach
                                @endif
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="{{ route('family.members') }}"><i class="bi bi-people me-2"></i>Manage Members</a></li>
                            </ul>
                        </div>

                        <!-- Primary Nav Links -->
                        <a class="nav-link {{ request()->routeIs('dashboard') ? 'active fw-bold text-primary' : '' }}" href="{{ route('dashboard') }}">
                            <i class="bi bi-grid-1x2 me-1"></i> Dashboard
                        </a>
                        <a class="nav-link {{ request()->routeIs('documents.*') ? 'active fw-bold text-primary' : '' }}" href="{{ route('documents.index') }}">
                            <i class="bi bi-folder2-open me-1"></i> Documents
                        </a>
                        <a class="nav-link {{ request()->routeIs('family.members') ? 'active fw-bold text-primary' : '' }}" href="{{ route('family.members') }}">
                            <i class="bi bi-people me-1"></i> Household
                        </a>
                        <a class="nav-link {{ request()->routeIs('settings.reminders') ? 'active fw-bold text-primary' : '' }}" href="{{ route('settings.reminders') }}">
                            <i class="bi bi-bell me-1"></i> Reminders
                        </a>
                    </div>
                    @endif

                    <!-- Right Side Actions -->
                    <div class="d-flex align-items-center gap-2 mt-2 mt-lg-0">
                        <a href="{{ route('documents.create') }}" class="btn btn-primary btn-sm d-flex align-items-center gap-1 shadow-sm">
                            <i class="bi bi-plus-lg"></i>
                            <span class="d-none d-md-inline">Add Document</span>
                        </a>

                        <div class="dropdown">
                            <button class="btn btn-sm btn-light border dropdown-toggle d-flex align-items-center gap-2" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-person-circle fs-5 text-secondary"></i>
                                <span class="d-none d-md-inline">{{ auth()->user()->name }}</span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                <li><h6 class="dropdown-header">{{ auth()->user()->email }}</h6></li>
                                <li><a class="dropdown-item" href="{{ route('settings.security') }}"><i class="bi bi-shield-lock me-2"></i>Security & Audit Log</a></li>
                                <li><a class="dropdown-item" href="{{ route('settings.reminders') }}"><i class="bi bi-sliders me-2"></i>Reminder Settings</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form action="{{ route('logout') }}" method="POST">
                                        @csrf
                                        <button type="submit" class="dropdown-item text-danger">
                                            <i class="bi bi-box-arrow-right me-2"></i>Sign Out
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    </div>
                @else
                    <div class="navbar-nav ms-auto gap-2">
                        <a href="{{ route('login') }}" class="btn btn-sm btn-outline-secondary">Sign In</a>
                        <a href="{{ route('register') }}" class="btn btn-sm btn-primary">Create Account</a>
                    </div>
                @endauth
            </div>
        </div>
    </nav>

    <!-- Global Alerts Container -->
    <div class="container-fluid px-lg-4 mt-3">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show d-flex align-items-center gap-2 shadow-sm" role="alert">
                <i class="bi bi-check-circle-fill fs-5"></i>
                <div>{{ session('success') }}</div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center gap-2 shadow-sm" role="alert">
                <i class="bi bi-exclamation-triangle-fill fs-5"></i>
                <div>{{ session('error') }}</div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if(session('info'))
            <div class="alert alert-info alert-dismissible fade show d-flex align-items-center gap-2 shadow-sm" role="alert">
                <i class="bi bi-info-circle-fill fs-5"></i>
                <div>{{ session('info') }}</div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                <div class="fw-bold mb-1"><i class="bi bi-exclamation-octagon-fill me-1"></i> Please check the form errors below:</div>
                <ul class="mb-0 ps-3">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
    </div>

    <!-- Main Content Area -->
    <main class="main-content py-4">
        @yield('content')
    </main>

    <!-- Global Footer -->
    <footer class="bg-white border-top py-4 mt-auto">
        <div class="container-fluid px-lg-4 text-center text-md-start">
            <div class="row align-items-center gy-3">
                <div class="col-md-6">
                    <p class="mb-1 text-muted small">
                        <strong>Family Document Expiry Tracker</strong> &copy; {{ date('Y') }}. Privacy-focused household record keeper.
                    </p>
                    <p class="mb-0 text-secondary" style="font-size: 0.775rem;">
                        <i class="bi bi-shield-shaded me-1"></i> Honest Status: Reminders are notifications; this application does not renew government documents or guarantee legal acceptance.
                    </p>
                </div>
                <div class="col-md-6 text-md-end">
                    <a href="{{ route('privacy') }}" class="text-decoration-none text-muted small me-3">Privacy Notice</a>
                    <a href="{{ route('terms') }}" class="text-decoration-none text-muted small me-3">Terms of Use</a>
                    <span class="text-muted small"><i class="bi bi-lock-fill text-success"></i> Private by Default</span>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap 5.3 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    @yield('scripts')
</body>
</html>

