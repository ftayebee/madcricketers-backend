<div class="main-nav">
    <!-- Sidebar Logo -->
    <div class="logo-box">
        <a href="index.html" class="logo-dark" style="text-align: center;">
            <img src="{{ asset((request()->getHost() === 'app.madcricketers.com' ? 'public/' : '') . 'storage/assets/images/Main-Logo.png') }}"
                class="logo-sm" alt="logo sm">
            <img src="{{ asset((request()->getHost() === 'app.madcricketers.com' ? 'public/' : '') . 'storage/assets/images/main-logo-dark.png') }}"
                class="logo-lg" alt="logo dark" height="45px">
        </a>

        <a href="index.html" class="logo-light" style="text-align: center;">
            <img src="{{ asset((request()->getHost() === 'app.madcricketers.com' ? 'public/' : '') . 'storage/assets/images/Main-Logo.png') }}"
                class="logo-sm" alt="logo sm">
            <img src="{{ asset((request()->getHost() === 'app.madcricketers.com' ? 'public/' : '') . 'storage/assets/images/main-logo-light.png') }}"
                class="logo-lg" alt="logo light" style="height: 45px!important;">
        </a>
    </div>

    <!-- Menu Toggle Button (sm-hover) -->
    <button type="button" class="button-sm-hover" aria-label="Show Full Sidebar">
        <i class="ri-menu-2-line fs-24 button-sm-hover-icon"></i>
    </button>

    <div class="scrollbar" data-simplebar>
        <div class="card" style="margin: 0 15px; background: transparent; border: none;">
            <div class="card-body">
                <div class="d-flex flex-wrap align-items-center gap-2 text-center justify-content-center">
                    <img src="{{ Auth::user()->image }}" alt=""
                        class="avatar-lg rounded-3 border border-light border-3">
                    <div class="d-block">
                        <p class="text-light fw-medium fs-16 mb-0">{{ Auth::user()->full_name }}</p>
                        <p class="mb-0 text-light">{{ Auth::user()->email }}</p>
                        <p class="mb-0 badge badge-soft-info fs-12 mt-1">
                            {{ ucfirst(optional(Auth::user()->primary_role)->name ?? 'user') }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <ul class="navbar-nav" id="navbar-nav">

            <li class="menu-title">Menu</li>

            <li class="nav-item">
                <a class="nav-link" href="{{ route('admin.dashboard') }}">
                    <span class="nav-icon">
                        <i class="ri-dashboard-2-line"></i>
                    </span>
                    <span class="nav-text"> Dashboards </span>
                </a>
            </li>

            <li class="menu-title">Main Menu</li>
            @if (Auth::check() && Auth::user()->can('players-view'))
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('admin.players.index') }}">
                        <span class="nav-icon">
                            <i class="ri-group-3-line"></i>
                        </span>
                        <span class="nav-text">Players</span>
                    </a>
                </li>
            @endif
            @if (Auth::check() && Auth::user()->can('teams-view'))
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('admin.teams.index') }}">
                        <span class="nav-icon">
                            <i class="ri-group-2-line"></i>
                        </span>
                        <span class="nav-text">Teams</span>
                    </a>
                </li>
            @endif

            @if (Auth::check() && Auth::user()->can('cricket-matches-view'))
                <li class="nav-item">
                    <a class="nav-link menu-arrow" href="#sidebarCricketMatches" data-bs-toggle="collapse" role="button"
                        aria-expanded="false" aria-controls="sidebarCricketMatches">
                        <span class="nav-icon">
                            <i class="ri-basketball-line"></i>
                        </span>
                        <span class="nav-text">Cricket Matches</span>
                    </a>
                    <div class="collapse" id="sidebarCricketMatches">
                        <ul class="nav sub-navbar-nav">
                            <li class="sub-nav-item">
                                <a class="sub-nav-link" href="{{ route('admin.cricket-matches.index') }}">All Matches</a>
                            </li>
                            {{-- <li class="sub-nav-item">
                                <a class="sub-nav-link" href="dashboard-agent.html">Add New</a>
                            </li> --}}
                        </ul>
                    </div>
                </li>
            @endif

            @if (Auth::check() && Auth::user()->can('tournaments-view'))
                <li class="nav-item">
                    <a class="nav-link menu-arrow" href="#sidebarTournaments" data-bs-toggle="collapse" role="button"
                        aria-expanded="false" aria-controls="sidebarTournaments">
                        <span class="nav-icon">
                            <i class="ri-folder-chart-line"></i>
                        </span>
                        <span class="nav-text">Tournaments</span>
                    </a>
                    <div class="collapse" id="sidebarTournaments">
                        <ul class="nav sub-navbar-nav">
                            <li class="sub-nav-item">
                                <a class="sub-nav-link" href="{{ route('admin.tournaments.index') }}">All Tournaments</a>
                            </li>
                            <li class="sub-nav-item">
                                <a class="sub-nav-link" href="{{ route('admin.tournaments.create') }}">Add New</a>
                            </li>
                        </ul>
                    </div>
                </li>
            @endif

            @php
                $authUser = Auth::user();
                $roleName = optional(optional($authUser)->primary_role)->name;
                $isFinanceAdmin = $authUser && (
                    in_array($roleName, ['admin', 'super-admin'], true) ||
                    (method_exists($authUser, 'hasRole') && ($authUser->hasRole('admin') || $authUser->hasRole('super-admin')))
                );
                $canFinanceView = $isFinanceAdmin || ($authUser && $authUser->can('finance-view'));
                $canFinanceDues = $isFinanceAdmin || ($authUser && $authUser->can('finance-dues-manage'));
                $canFinancePayments = $isFinanceAdmin || ($authUser && $authUser->can('finance-payments-manage'));
                $canFinanceExpenses = $isFinanceAdmin || ($authUser && $authUser->can('finance-expenses-manage'));
                $canFinanceCategories = $isFinanceAdmin || ($authUser && $authUser->can('finance-categories-manage'));
                $canFinanceReports = $isFinanceAdmin || ($authUser && $authUser->can('finance-reports-view'));
                $showFinanceMenu = $canFinanceView || $canFinanceDues || $canFinancePayments || $canFinanceExpenses || $canFinanceCategories || $canFinanceReports;
                $isFinanceOpen = Route::is('admin.finance.*') || request()->is('admin/finance*');
            @endphp

            @if (Auth::check() && $showFinanceMenu)
                <li class="nav-item">
                    <a class="nav-link menu-arrow {{ $isFinanceOpen ? 'active' : '' }}" href="#sidebarFinance"
                        data-bs-toggle="collapse" role="button" aria-expanded="{{ $isFinanceOpen ? 'true' : 'false' }}"
                        aria-controls="sidebarFinance">
                        <span class="nav-icon">
                            <i class="ri-money-dollar-circle-line"></i>
                        </span>
                        <span class="nav-text">Finance</span>
                    </a>
                    <div class="collapse {{ $isFinanceOpen ? 'show' : '' }}" id="sidebarFinance">
                        <ul class="nav sub-navbar-nav">
                            @if ($canFinanceView)
                                <li class="sub-nav-item">
                                    <a class="sub-nav-link {{ Route::is('admin.finance.dashboard') ? 'active' : '' }}"
                                        href="{{ route('admin.finance.dashboard') }}">Dashboard</a>
                                </li>
                            @endif
                            @if ($canFinanceDues)
                                <li class="sub-nav-item">
                                    <a class="sub-nav-link {{ Route::is('admin.finance.dues.index') ? 'active' : '' }}"
                                        href="{{ route('admin.finance.dues.index') }}">Player Dues</a>
                                </li>
                            @endif
                            @if ($canFinancePayments)
                                <li class="sub-nav-item">
                                    <a class="sub-nav-link {{ Route::is('admin.finance.payments.create') ? 'active' : '' }}"
                                        href="{{ route('admin.finance.payments.create') }}">Receive Payment</a>
                                </li>
                                <li class="sub-nav-item">
                                    <a class="sub-nav-link {{ Route::is('admin.finance.payments.index') ? 'active' : '' }}"
                                        href="{{ route('admin.finance.payments.index') }}">Payment History</a>
                                </li>
                            @endif
                            @if ($canFinanceExpenses)
                                <li class="sub-nav-item">
                                    <a class="sub-nav-link {{ Route::is('admin.finance.expenses.index') ? 'active' : '' }}"
                                        href="{{ route('admin.finance.expenses.index') }}">Expenses</a>
                                </li>
                            @endif
                            @if ($canFinanceCategories)
                                <li class="sub-nav-item">
                                    <a class="sub-nav-link {{ Route::is('admin.finance.categories.index') ? 'active' : '' }}"
                                        href="{{ route('admin.finance.categories.index') }}">Categories</a>
                                </li>
                            @endif
                            @if ($canFinanceReports)
                                <li class="sub-nav-item">
                                    <a class="sub-nav-link {{ Route::is('admin.finance.reports.index') || Route::is('admin.finance.summary') ? 'active' : '' }}"
                                        href="{{ route('admin.finance.reports.index') }}">Reports</a>
                                </li>
                            @endif
                        </ul>
                    </div>
                </li>
            @endif

            <li class="menu-title">System Settings</li>
            @if (Auth::check() && Auth::user()->can('roles-view'))
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('admin.settings.roles.index') }}">
                        <span class="nav-icon">
                            <i class="ri-shield-star-line"></i>
                        </span>
                        <span class="nav-text">Roles</span>
                    </a>
                </li>
            @endif

            @if (Auth::check() && Auth::user()->can('users-view'))
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('admin.settings.users.index') }}">
                        <span class="nav-icon">
                            <i class="ri-group-line"></i>
                        </span>
                        <span class="nav-text">Users</span>
                    </a>
                </li>
            @endif
        </ul>
    </div>
</div>