<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\TournamentController;
use App\Http\Controllers\API\CricketMatchController;

Route::get('/recent-matches', [CricketMatchController::class, 'recentMatches'])->name('api.recent-matches');
Route::get('/completed-matches', [CricketMatchController::class, 'completedMatches'])->name('api.completed-matches');
Route::get('/tournaments', [TournamentController::class, 'getTournaments'])->name('api.tournaments');
Route::get('/tournaments/{slug}', [TournamentController::class, 'getTournamentBySlug'])->name('api.tournaments.show');
