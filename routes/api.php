<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\TournamentController;
use App\Http\Controllers\API\CricketMatchController;
use App\Http\Controllers\API\PlayerController;

Route::post('/registration', [PlayerController::class, 'register'])->name('api.register');
Route::get('/boot-slug', [PlayerController::class, 'boot'])->name('api.boot');

Route::prefix('matches')->group(function(){
    Route::get('/upcoming', [CricketMatchController::class, 'getUpcomingMatches'])->name('api.recent-matches');
    Route::get('/completed', [CricketMatchController::class, 'completedMatches'])->name('api.completed-matches');
    Route::get('/live', [CricketMatchController::class, 'getLiveMatches'])->name('api.live-matches');
    Route::get('/detail/{id}', [CricketMatchController::class, 'getMatchDetailBySlug'])->name('api.detail-matches');
    Route::get('/detail/team-info/{id}', [CricketMatchController::class, 'getMatchInfo'])->name('api.detail-matches.teamForm');
    Route::get('/yet-to-bat/{id}', [CricketMatchController::class, 'getYetToBat'])->name('api.matches.yet-to-bat');

    Route::get('/get-team-data', [CricketMatchController::class, 'getTeamInfo'])->name('api.matches.team-info');
});

Route::prefix('tournaments')->group(function(){
    Route::get('/', [TournamentController::class, 'getTournaments'])->name('api.tournaments');
    Route::get('/{slug}', [TournamentController::class, 'getTournamentBySlug'])->name('api.tournaments.show');
});


Route::prefix('players')->group(function(){
    Route::get('/', [PlayerController::class, 'getAllPlayers'])->name('api.players.list');
    Route::get('/key-stats', [PlayerController::class, 'getKeyStats'])->name('api.players.key-stats');
    Route::get('/search', [PlayerController::class, 'searchPlayers'])->name('api.players.search');
    Route::get('/detail/{slug}', [PlayerController::class, 'getPlayerBySlug'])->name('api.players.show');
});