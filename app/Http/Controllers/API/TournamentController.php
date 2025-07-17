<?php

namespace App\Http\Controllers\API;

use Carbon\Carbon;
use App\Models\Tournament;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;

class TournamentController extends Controller
{
    public function getTournaments(Request $request)
    {
        try {
            $tournaments = Tournament::orderBy('start_date', 'desc')->get();

            Log::info("Tournaments Count: " . $tournaments->count());

            $data = $tournaments->map(function ($tournament) {
                return [
                    'id' => $tournament->id,
                    'name' => $tournament->name,
                    'start_date' => Carbon::parse($tournament->start_date)->format('Y-m-d'),
                    'end_date' => Carbon::parse($tournament->end_date)->format('Y-m-d'),
                    'status' => $tournament->status,
                    'matches_count' => $tournament->matches->count(),
                    'stage' => implode(", ", array_unique($tournament->matches->pluck('stage')->toArray()))
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'Tournaments fetched successfully.',
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching tournaments', [
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'message' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch tournaments.',
            ], 500);
        }
    }
}
