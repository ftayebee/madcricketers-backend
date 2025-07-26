<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\TournamentController;
use App\Http\Controllers\API\CricketMatchController;

Route::prefix('matches')->group(function(){
    Route::get('/upcoming', [CricketMatchController::class, 'recentMatches'])->name('api.recent-matches');
    Route::get('/completed', [CricketMatchController::class, 'completedMatches'])->name('api.completed-matches');
    Route::get('/live', [CricketMatchController::class, 'completedMatches'])->name('api.live-matches');
    Route::get('/detail/{id}', [CricketMatchController::class, 'getMatchDetailBySlug'])->name('api.detail-matches');
    Route::get('/detail/team-info/{id}', [CricketMatchController::class, 'getMatchInfo'])->name('api.detail-matches.teamForm');
    Route::get('/yet-to-bat/{id}', [CricketMatchController::class, 'getYetToBat'])->name('api.matches.yet-to-bat');
});

Route::prefix('tournaments')->group(function(){
    Route::get('/', [TournamentController::class, 'getTournaments'])->name('api.tournaments');
    Route::get('/{slug}', [TournamentController::class, 'getTournamentBySlug'])->name('api.tournaments.show');
});
