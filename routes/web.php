<?php

use App\Http\Controllers\Admin\CricketMatchController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\PageController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\TeamController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\PlayerController;
use App\Http\Controllers\Admin\TournamentController;
use App\Http\Controllers\Frontend\PageController as FrontendPageController;

Route::get('/', function () {
    return redirect('/login');
});
Auth::routes();

// Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
Route::prefix('admin')->name('admin.')->group(function(){
    Route::get('/dashboard', [PageController::class, 'dashboard'])->name('dashboard'); // working
    Route::get('/profile', [PageController::class, 'profile'])->name('profile'); // working

    Route::prefix('settings')->name('settings.')->group(function(){
        Route::prefix('roles')->name('roles.')->group(function(){
            Route::get('/', [RoleController::class, 'index'])->name('index'); // working
            Route::get('/loader', [RoleController::class, 'tableLoader'])->name('loader'); // working
            Route::get('/show/{slug}', [RoleController::class, 'show'])->name('show'); // working
            Route::post('/store', [RoleController::class, 'store'])->name('store'); // working
            Route::post('/update', [RoleController::class, 'update'])->name('update'); // working
            Route::post('/destroy', [RoleController::class, 'destroy'])->name('destroy'); // working
            Route::get('/seed-database', [RoleController::class, 'seedDatabase'])->name('seed'); // working
        });

        Route::prefix('permissions')->name('permissions.')->group(function () {
            Route::post('/update', [RoleController::class, 'permissionUpdate'])->name('update'); // working
        });

        Route::prefix('users')->name('users.')->group(function () {
            Route::get('/', [UserController::class, 'index'])->name('index'); // working
            Route::get('/loader', [UserController::class, 'loader'])->name('loader'); // working
            Route::get('/create', [UserController::class, 'create'])->name('create'); // working
            Route::get('/show/{id}', [UserController::class, 'show'])->name('show'); // working
            Route::get('/edit/{id}', [UserController::class, 'edit'])->name('edit');
            Route::post('/store', [UserController::class, 'store'])->name('store'); // working
            Route::post('/update/{id}', [UserController::class, 'update'])->name('update');
            Route::post('/destroy/{id}', [UserController::class, 'destroy'])->name('destroy'); // working
        });
    });

    Route::prefix('players')->name('players.')->group(function(){
        Route::get('/', [PlayerController::class, 'index'])->name('index'); // working
        Route::get('/loader', [PlayerController::class, 'tableLoader'])->name('loader'); // working
        Route::get('/show/{id}', [PlayerController::class, 'show'])->name('show'); // working
        Route::post('/destroy', [PlayerController::class, 'destroy'])->name('destroy'); // working
        Route::post('/approve/{id}', [PlayerController::class, 'approve'])->name('approve'); // working
    });

    Route::prefix('teams')->name('teams.')->group(function(){
        Route::get('/', [TeamController::class, 'index'])->name('index');  // working
        Route::get('/loader', [TeamController::class, 'tableLoader'])->name('loader');  // working
        Route::get('/show/{id}', [TeamController::class, 'show'])->name('show');  // working
        Route::post('/store', [TeamController::class, 'store'])->name('store');  // working
        Route::post('/update/{id}', [TeamController::class, 'update'])->name('update');
        Route::post('/destroy/{id}', [TeamController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('tournaments')->name('tournaments.')->group(function(){
        Route::get('/', [TournamentController::class, 'index'])->name('index');
        Route::get('/loader', [TournamentController::class, 'tableLoader'])->name('loader');
        Route::get('/create', [TournamentController::class, 'create'])->name('create');
        Route::get('/show/{slug}', [TournamentController::class, 'show'])->name('show');
        Route::get('/edit', [TournamentController::class, 'edit'])->name('edit');
        Route::post('/store', [TournamentController::class, 'store'])->name('store');
        Route::post('/update/{id}', [TournamentController::class, 'update'])->name('update');
        Route::post('/destroy/{id}', [TournamentController::class, 'destroy'])->name('destroy');

        Route::post('/assign-teams', [TournamentController::class, 'assignTeams'])->name('assign-teams');
        Route::post('/generate-fixtures', [TournamentController::class, 'generateFixtures'])->name('generate-fixtures');
    });

    Route::prefix('cricket-matches')->name('cricket-matches.')->group(function(){
        Route::get('/', [CricketMatchController::class, 'index'])->name('index');
        Route::get('/loader', [CricketMatchController::class, 'tableLoader'])->name('loader');
        Route::get('/create', [CricketMatchController::class, 'create'])->name('create');
        Route::get('/show/{id}', [CricketMatchController::class, 'show'])->name('show');
        Route::get('/edit', [CricketMatchController::class, 'edit'])->name('edit');
        Route::post('/store', [CricketMatchController::class, 'store'])->name('store');
        Route::post('/update/{id}', [CricketMatchController::class, 'update'])->name('update');
        Route::post('/destroy/{id}', [CricketMatchController::class, 'destroy'])->name('destroy');

        Route::get('/start/{id}', [CricketMatchController::class, 'startCricketMatch'])->name('start');
        Route::post('/toss/store', [CricketMatchController::class, 'storeToss'])->name('toss.store');

        Route::prefix('scoreboard')->name('scoreboard.')->group(function(){
            Route::post('/select-batsman', [CricketMatchController::class, 'selectBatsman'])->name('select-batsman');
            Route::post('/set-striker', [CricketMatchController::class, 'setStriker'])->name('set-striker');
            Route::get('/load-current-stats', [CricketMatchController::class, 'loadCurrentStats'])->name('load-current-stats');
            Route::get('/mark-innings-complete', [CricketMatchController::class, 'setInningsStatus'])->name('mark-innings-complete');
            Route::get('/match-info', [CricketMatchController::class, 'getMatchInfo'])->name('match-info');
            Route::post('/switch-strike', [CricketMatchController::class, 'switchStrike'])->name('switch-strike');
            Route::get('/change-bowler', [CricketMatchController::class, 'changeBowler'])->name('change-bowler');
            Route::post('/store-delivery', [CricketMatchController::class, 'storeDelivery'])->name('store-delivery');
            Route::post('/store-wickets', [CricketMatchController::class, 'storeWickets'])->name('store-wickets');
            Route::get('/{match}/team-b-players', [CricketMatchController::class, 'getTeamBPlayers']);
            Route::post('/{match}/add-bowler', [CricketMatchController::class, 'chooseBowler'])->name('choose-bowler');
            Route::get('/{match}/current-over', [CricketMatchController::class, 'getCurrentOver'])->name('current-over');
        });
    });
});

// FRONTEND ROUTES
// Route::get('/', [FrontendPageController::class, 'index'])->name('frontend.home'); // working
