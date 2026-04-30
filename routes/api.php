<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\TournamentController;
use App\Http\Controllers\API\CricketMatchController;
use App\Http\Controllers\API\MatchManagementController;
use App\Http\Controllers\API\PlayerAuthController;
use App\Http\Controllers\API\PlayerController;
use App\Http\Controllers\API\ScoreboardManagementController;
use App\Http\Controllers\API\TeamManagementController;

Route::get('/ping', [AuthController::class, 'ping'])->name('api.ping');
Route::post('/login', [AuthController::class, 'login'])->name('api.login');

Route::prefix('player')->name('api.player.')->group(function () {
    Route::post('/login', [PlayerAuthController::class, 'login'])->name('login');

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [PlayerAuthController::class, 'logout'])->name('logout');
        Route::get('/profile', [PlayerAuthController::class, 'profile'])->name('profile');
        Route::put('/profile', [PlayerAuthController::class, 'updateProfile'])->name('profile.update');
        Route::post('/profile', [PlayerAuthController::class, 'updateProfile'])->name('profile.update.post');
        Route::get('/stats/summary', [PlayerAuthController::class, 'statsSummary'])->name('stats.summary');
        Route::get('/stats/matches', [PlayerAuthController::class, 'matchStats'])->name('stats.matches');
        Route::get('/stats/tournaments', [PlayerAuthController::class, 'tournamentStats'])->name('stats.tournaments');
    });
});

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('api.logout');
    Route::get('/me', [AuthController::class, 'me'])->name('api.me');

    Route::apiResource('teams', TeamManagementController::class);
    Route::post('/teams/{team}/players', [TeamManagementController::class, 'assignPlayers'])->name('api.teams.players.assign');
    Route::delete('/teams/{team}/players/{player}', [TeamManagementController::class, 'removePlayer'])->name('api.teams.players.remove');

    Route::get('/matches', [MatchManagementController::class, 'index'])->name('api.matches.index');
    Route::post('/matches', [MatchManagementController::class, 'store'])->name('api.matches.store');
    Route::get('/matches/{match}', [MatchManagementController::class, 'show'])->where('match', '[0-9]+')->name('api.matches.show');
    Route::put('/matches/{match}', [MatchManagementController::class, 'update'])->where('match', '[0-9]+')->name('api.matches.update');
    Route::post('/matches/{match}/start', [MatchManagementController::class, 'start'])->where('match', '[0-9]+')->name('api.matches.start');
    Route::post('/matches/{match}/toss', [MatchManagementController::class, 'toss'])->where('match', '[0-9]+')->name('api.matches.toss');

    Route::get('/matches/{match}/scoreboard', [ScoreboardManagementController::class, 'show'])->where('match', '[0-9]+')->name('api.matches.scoreboard.show');
    Route::post('/matches/{match}/score', [ScoreboardManagementController::class, 'score'])->where('match', '[0-9]+')->name('api.matches.scoreboard.score');
    Route::post('/matches/{match}/wicket', [ScoreboardManagementController::class, 'wicket'])->where('match', '[0-9]+')->name('api.matches.scoreboard.wicket');
    Route::post('/matches/{match}/extras', [ScoreboardManagementController::class, 'extras'])->where('match', '[0-9]+')->name('api.matches.scoreboard.extras');
    Route::post('/matches/{match}/undo', [ScoreboardManagementController::class, 'undo'])->where('match', '[0-9]+')->name('api.matches.scoreboard.undo');
    Route::post('/matches/{match}/end-innings', [ScoreboardManagementController::class, 'endInnings'])->where('match', '[0-9]+')->name('api.matches.scoreboard.end-innings');
    Route::post('/matches/{match}/complete', [ScoreboardManagementController::class, 'complete'])->where('match', '[0-9]+')->name('api.matches.scoreboard.complete');
    Route::post('/matches/{match}/select-batsman', [ScoreboardManagementController::class, 'selectBatsman'])->where('match', '[0-9]+')->name('api.matches.scoreboard.select-batsman');
    Route::post('/matches/{match}/select-bowler', [ScoreboardManagementController::class, 'selectBowler'])->where('match', '[0-9]+')->name('api.matches.scoreboard.select-bowler');
});

Route::post('/registration', [PlayerController::class, 'register'])->name('api.register');
Route::get('/boot-slug', [PlayerController::class, 'boot'])->name('api.boot');

Route::prefix('matches')->group(function(){
    Route::get('/upcoming', [CricketMatchController::class, 'getUpcomingMatches'])->name('api.recent-matches');
    Route::get('/completed', [CricketMatchController::class, 'completedMatches'])->name('api.completed-matches');
    Route::get('/live', [CricketMatchController::class, 'getLiveMatches'])->name('api.live-matches');
    Route::get('/detail/{id}', [CricketMatchController::class, 'getMatchDetailBySlug'])->name('api.detail-matches');
    Route::get('/detail/team-info/{id}', [CricketMatchController::class, 'getMatchInfo'])->name('api.detail-matches.teamForm');
    Route::get('/yet-to-bat/{id}', [CricketMatchController::class, 'getYetToBat'])->name('api.matches.yet-to-bat');
    Route::get('/scorecard', [CricketMatchController::class, 'getScorecard'])->name('api.matches.scorecard');
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
