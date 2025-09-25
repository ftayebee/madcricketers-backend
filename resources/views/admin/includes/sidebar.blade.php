<div class="main-nav">
    <!-- Sidebar Logo -->
    <div class="logo-box">
        <a href="index.html" class="logo-dark" style="text-align: center;">
            <img src="{{ asset('storage/assets/images/Main-Logo.png') }}" class="logo-sm" alt="logo sm" >
            <img src="{{ asset('storage/assets/images/main-logo-dark.png') }}" class="logo-lg" alt="logo dark" height="45px">
        </a>

        <a href="index.html" class="logo-light" style="text-align: center;">
            <img src="{{ asset('storage/assets/images/Main-Logo.png') }}" class="logo-sm" alt="logo sm">
            <img src="{{ asset('storage/assets/images/main-logo-light.png') }}" class="logo-lg" alt="logo light" style="height: 45px!important;">
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
                    <img src="{{Auth::user()->image}}" alt=""
                        class="avatar-lg rounded-3 border border-light border-3">
                    <div class="d-block">
                        <p class="text-light fw-medium fs-16 mb-0">{{Auth::user()->full_name}}</p>
                        <p class="mb-0 text-light">{{Auth::user()->email}}</p>
                        <p class="mb-0 badge badge-soft-info fs-12 mt-1">{{ucfirst(Auth::user()->role->name)}}</p>
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
                            <i class="ri-shield-star-line"></i>
                        </span>
                        <span class="nav-text">Players</span>
                    </a>
                </li>
            @endif
            @if (Auth::check() && Auth::user()->can('teams-view'))
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('admin.teams.index') }}">
                        <span class="nav-icon">
                            <i class="ri-shield-star-line"></i>
                        </span>
                        <span class="nav-text">Teams</span>
                    </a>
                </li>
            @endif

            @if (Auth::check() && Auth::user()->can('cricket-matches-view'))
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('admin.cricket-matches.index') }}">
                        <span class="nav-icon">
                            <i class="ri-shield-star-line"></i>
                        </span>
                        <span class="nav-text">Matches</span>
                    </a>
                </li>
            @endif

            @if (Auth::check() && Auth::user()->can('tournaments-view'))
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('admin.tournaments.index') }}">
                        <span class="nav-icon">
                            <i class="ri-shield-star-line"></i>
                        </span>
                        <span class="nav-text">Tournaments</span>
                    </a>
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
                            <i class="ri-shield-star-line"></i>
                        </span>
                        <span class="nav-text">Users</span>
                    </a>
                </li>
            @endif
        </ul>
    </div>
</div>
