<nav class="navbar navbar-expand-lg">
    <div class="container">
        <a class="navbar-brand" href="{{ route('frontend.home') }}">
            <img src="{{ asset('storage/frontend/images/main-logo.png') }}" alt="CricketLive Logo" height="40">
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link" href="{{ route('frontend.home') }}">Home</a></li>
                <li class="nav-item"><a class="nav-link" href="#">Matches</a></li>
                <li class="nav-item"><a class="nav-link" href="#">Players</a></li>
            </ul>
        </div>
    </div>
</nav>
