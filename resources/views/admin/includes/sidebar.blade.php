<div class="main-nav">
    <!-- Sidebar Logo -->
    <div class="logo-box">
        <a href="index.html" class="logo-dark">
            <img src="{{asset('storage/assets/images/logo-sm.png') }}" class="logo-sm" alt="logo sm">
            <img src="{{asset('storage/assets/images/logo-dark.png') }}" class="logo-lg" alt="logo dark">
        </a>

        <a href="index.html" class="logo-light">
            <img src="{{asset('storage/assets/images/logo-sm.png') }}" class="logo-sm" alt="logo sm">
            <img src="{{asset('storage/assets/images/logo-light.png') }}" class="logo-lg" alt="logo light">
        </a>
    </div>

    <!-- Menu Toggle Button (sm-hover) -->
    <button type="button" class="button-sm-hover" aria-label="Show Full Sidebar">
        <i class="ri-menu-2-line fs-24 button-sm-hover-icon"></i>
    </button>

    <div class="scrollbar" data-simplebar>

        <ul class="navbar-nav" id="navbar-nav">

            <li class="menu-title">Menu</li>

            <li class="nav-item">
                <a class="nav-link" href="{{route('admin.dashboard')}}">
                    <span class="nav-icon">
                        <i class="ri-dashboard-2-line"></i>
                    </span>
                    <span class="nav-text"> Dashboards </span>
                </a>
            </li>

            <li class="menu-title">Main Menu</li>
            @if(Auth::user()->can('players-view'))
            <li class="nav-item">
                <a class="nav-link" href="{{route('admin.players.index')}}">
                    <span class="nav-icon">
                        <i class="ri-shield-star-line"></i>
                    </span>
                    <span class="nav-text">Players</span>
                </a>
            </li>
            @endif
            @if(Auth::user()->can('teams-view'))
            <li class="nav-item">
                <a class="nav-link" href="{{route('admin.teams.index')}}">
                    <span class="nav-icon">
                        <i class="ri-shield-star-line"></i>
                    </span>
                    <span class="nav-text">Teams</span>
                </a>
            </li>
            @endif

            <li class="menu-title">System Settings</li>
            @if(Auth::user()->can('roles-view'))
            <li class="nav-item">
                <a class="nav-link" href="{{route('admin.settings.roles.index')}}">
                    <span class="nav-icon">
                        <i class="ri-shield-star-line"></i>
                    </span>
                    <span class="nav-text">Roles</span>
                </a>
            </li>
            @endif
            @if(Auth::user()->can('users-view'))
            <li class="nav-item">
                <a class="nav-link" href="{{route('admin.settings.users.index')}}">
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
