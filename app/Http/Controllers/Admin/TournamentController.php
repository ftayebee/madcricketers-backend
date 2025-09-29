<?php

namespace App\Http\Controllers\Admin;

use Exception;
use Carbon\Carbon;
use App\Models\Team;
use Carbon\CarbonPeriod;
use App\Models\Tournament;
use Illuminate\Support\Str;
use App\Models\CricketMatch;
use Illuminate\Http\Request;
use App\Models\TournamentGroup;
use App\Models\TournamentGroupTeam;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Intervention\Image\Facades\Image;
use Illuminate\Validation\ValidationException;

class TournamentController extends Controller
{
    protected $module = 'tournaments';

    public function index()
    {
        try {
            if (!Auth::user()->can($this->module . '-view')) {
                throw new Exception('Unauthorized Access');
            }

            session([
                'title' => 'Tournaments Management',
                'breadcrumbs' => [
                    'home' => [
                        'url' => route('admin.dashboard'),
                        'name' => 'Dashboard'
                    ],
                    $this->module => [
                        'url' => route('admin.tournaments.index'),
                        'name' => 'Tournaments Management'
                    ]
                ]
            ]);

            $today = Carbon::today();
            $teamsInFutureTournaments = TournamentGroupTeam::whereHas('group.tournament', function ($query) use ($today) {
                $query->where('start_date', '>', $today);
            })->pluck('team_id')->toArray();
            $validTeams = Team::whereNotIn('id', $teamsInFutureTournaments)->get();

            return view('admin.pages.tournaments.index', compact('validTeams'));
        } catch (Exception $e) {
            Log::error("Error Loading tournaments Management", [
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'message' => $e->getMessage()
            ]);

            return redirect()->back()->with([
                'success' => false,
                'message' => 'Error Loading tournaments Management.'
            ]);
        }
    }

    public function tableLoader(Request $request)
    {
        try {
            if (!Auth::user()->can($this->module . '-view')) {
                throw new Exception('Unauthorized Access');
            }

            $teams = Tournament::orderBy('created_at', 'desc')->get();

            $formattedData =  $teams->map(function ($item) {
                $viewUrl = route('admin.tournaments.show', $item->slug);

                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'slug' => $item->slug,
                    'location' => $item->location,
                    'description' => $item->description,
                    'start_date' => Carbon::parse($item->start_date)->format('d M, Y'),
                    'end_date' => Carbon::parse($item->end_date)->format('d M, Y'),
                    'status' => $item->status,
                    'trophy_image' => $item->trophy_image,
                    'playing_teams' => $item->groups->flatMap->teams->unique('id')->count(),
                    'logo' => $item->logo,
                    'format' => $item->format,
                    'viewUrl' => $viewUrl,
                    'canView' => !Auth::user()->can($this->module . '-view') ? false : true,
                    'canDelete' => !Auth::user()->can($this->module . '-delete') ? false : true,
                    'canAssignTeam' => !Auth::user()->can($this->module . '-assign-teams') ? false : true,
                ];
            });

            return response()->json(['data' => $formattedData]);
        } catch (Exception $e) {
            Log::error("Error Loading teams table", [
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'message' => $e->getMessage()
            ]);

            return redirect()->back()->with([
                'success' => false,
                'message' => 'Error Loading teams table.'
            ]);
        }
    }

    public function create()
    {
        try {
            if (!Auth::user()->can($this->module . '-create')) {
                throw new Exception('Unauthorized Access');
            }

            session([
                'title' => 'Tournaments Create',
                'breadcrumbs' => [
                    'home' => [
                        'url' => route('admin.dashboard'),
                        'name' => 'Dashboard'
                    ],
                    $this->module => [
                        'url' => route('admin.tournaments.index'),
                        'name' => 'Tournaments Management'
                    ],
                    $this->module . '-create' => [
                        'url' => route('admin.tournaments.create'),
                        'name' => 'Create New'
                    ]
                ]
            ]);

            $today = Carbon::today();
            $teamsInFutureTournaments = TournamentGroupTeam::whereHas('group.tournament', function ($query) use ($today) {
                $query->where('start_date', '>', $today);
            })->pluck('team_id')->toArray();
            $validTeams = Team::whereNotIn('id', $teamsInFutureTournaments)->get();

            return view('admin.pages.tournaments.create', compact('validTeams'));
        } catch (Exception $e) {
            Log::error("Error Loading tournaments create", [
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'message' => $e->getMessage()
            ]);

            return redirect()->back()->with([
                'success' => false,
                'message' => 'Error Loading tournaments create.'
            ]);
        }
    }

    public function store(Request $request)
    {
        try {
            if (!Auth::user()->can($this->module . '-create')) {
                throw new Exception('Unauthorized Access');
            }

            $validated = $request->validate([
                'name'          => 'required|string|max:255',
                'slug'          => 'nullable|string|max:255',
                'location'      => 'nullable|string|max:255',
                'description'   => 'nullable|string',
                'start_date'    => 'nullable|date',
                'end_date'      => 'nullable|date|after_or_equal:start_date',
                'status'        => 'required|in:upcoming,ongoing,completed',
                'logo'          => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
                'trophy_image'  => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
                'format'        => 'required|in:group,round-robin,knockout',
                'group_count'   => 'nullable|numeric',
                'team_id'       => 'nullable|array|min:1',
                'team_id.*'     => 'exists:teams,id',
                'seperate_teams' => 'nullable|in:on',
                'overs_per_innings' => 'required|numeric',
            ]);

            $tournament = new Tournament();
            $tournament->name        = $validated['name'];
            $tournament->slug        = Str::slug($validated['slug'] ?? $validated['name']);
            $tournament->location    = $validated['location'] ?? null;
            $tournament->description = $validated['description'] ?? null;
            $tournament->start_date  = $validated['start_date'] ?? null;
            $tournament->end_date    = $validated['end_date'] ?? null;
            $tournament->status      = $validated['status'];
            $tournament->format      = $validated['format'];
            $tournament->group_count = $validated['format'] == 'group' ? $validated['group_count'] : null;
            $tournament->overs_per_innings = $validated['overs_per_innings'];

            if ($request->hasFile('logo')) {
                $logo = $request->file('logo');
                $logoFilename = 'tournament_logo_' . time() . '.' . $logo->getClientOriginalExtension();

                $logoPath = storage_path('app/public/uploads/tournaments');
                if (!file_exists($logoPath)) {
                    mkdir($logoPath, 0775, true);
                }

                Image::make($logo)->save($logoPath . '/' . $logoFilename);
                $tournament->logo = $logoFilename;
            }

            if ($request->hasFile('trophy_image')) {
                $trophy = $request->file('trophy_image');
                $trophyFilename = 'trophy_' . time() . '.' . $trophy->getClientOriginalExtension();

                $trophyPath = storage_path('app/public/uploads/tournaments');
                if (!file_exists($trophyPath)) {
                    mkdir($trophyPath, 0775, true);
                }

                Image::make($trophy)->save($trophyPath . '/' . $trophyFilename);
                $tournament->trophy_image = $trophyFilename;
            }

            $tournament->save();
            $tournament->refresh();

            if (!empty($validated['team_id'])) {
                $separate = $request->has('seperate_teams');

                if ($separate && $tournament->format == 'group' && !empty($tournament->group_count)) {
                    if ($tournament->groups()->count() === 0) {
                        for ($i = 0; $i < $tournament->group_count; $i++) {
                            $groupName = 'Group ' . chr(65 + $i);
                            $tournament->groups()->create(['name' => $groupName]);
                        }
                        $tournament->load('groups');
                    }

                    $teams = $validated['team_id'];
                    shuffle($teams);

                    $groupCount = $tournament->groups->count();
                    $groups = $tournament->groups;

                    foreach ($teams as $index => $teamId) {
                        $groupIndex = $index % $groupCount;
                        TournamentGroupTeam::create([
                            'group_id' => $groups[$groupIndex]->id,
                            'team_id' => $teamId,
                            'tournament_id' => $tournament->id,
                        ]);
                    }
                } else {
                    $group = TournamentGroup::create([
                        'tournament_id' => $tournament->id,
                        'name' => 'Group A',
                    ]);

                    foreach ($validated['team_id'] as $teamId) {
                        TournamentGroupTeam::create([
                            'group_id' => $group->id,
                            'team_id' => $teamId,
                            'tournament_id' => $tournament->id,
                        ]);
                    }
                }
            }

            return redirect()->route('admin.tournaments.index')->with([
                'success' => true,
                'message' => 'Tournament has been created.',
            ]);
        } catch (ValidationException $e) {
            Log::error('Validation failed in TournamentController@store', [
                'errors' => $e->validator->errors()->toArray(),
                'input'  => $request->all(),
            ]);

            return redirect()->back()
                ->withErrors($e->validator)
                ->withInput()
                ->with([
                    'success' => false,
                    'message' => 'Invalid tournament data submitted.',
                ]);
        } catch (Exception $e) {
            Log::error("Error saving tournament data", [
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'message' => $e->getMessage(),
            ]);

            return redirect()->back()->with([
                'success' => false,
                'message' => 'Error saving tournament data.',
            ]);
        }
    }

    public function assignTeams(Request $request)
    {
        try {
            if (!Auth::user()->can($this->module . '-assign-teams')) {
                throw new Exception('Unauthorized Access');
            }

            $validated = $request->validate([
                'tournament_id' => 'required|exists:tournaments,id',
                'team_id'       => 'required|array|min:1',
                'team_id.*'     => 'exists:teams,id',
                'seperate_teams' => 'nullable|in:on',
            ]);

            $tournament = Tournament::with('groups')->findOrFail($validated['tournament_id']);
            $teams = $validated['team_id'];
            $separate = $request->has('seperate_teams');

            // Clear existing group teams & groups if any before assigning new
            if ($tournament->groups()->count() > 0 && Carbon::now() < Carbon::parse($tournament->start_date)) {
                foreach ($tournament->groups as $group) {
                    $group->teams()->detach();
                }
                if ($separate) {
                    $tournament->groups()->delete();
                }
            }

            if ($separate && $tournament->format == 'group' && !empty($tournament->group_count)) {
                if ($tournament->groups()->count() === 0) {
                    for ($i = 0; $i < $tournament->group_count; $i++) {
                        $groupName = 'Group ' . chr(65 + $i); // Group A, B, ...
                        $tournament->groups()->create(['name' => $groupName]);
                    }
                    $tournament->load('groups');
                }

                shuffle($teams);

                $groupCount = $tournament->groups->count();
                $groups = $tournament->groups;

                foreach ($teams as $index => $teamId) {
                    $groupIndex = $index % $groupCount;

                    TournamentGroupTeam::create([
                        'group_id' => $groups[$groupIndex]->id,
                        'team_id' => $teamId,
                        'tournament_id' => $tournament->id,
                    ]);
                }
            } else {
                $group = TournamentGroup::create([
                    'tournament_id' => $tournament->id,
                    'name' => 'Group A',
                ]);

                // Attach all selected teams to the default group
                foreach ($teams as $teamId) {
                    TournamentGroupTeam::create([
                        'group_id' => $group->id,
                        'team_id' => $teamId,
                        'tournament_id' => $tournament->id,
                    ]);
                }
            }

            return redirect()->back()->with([
                'success' => true,
                'message' => 'Teams assigned to tournament successfully.',
            ]);
        } catch (ValidationException $e) {
            Log::error('Validation failed in assignTeams', [
                'errors' => $e->validator->errors()->toArray(),
                'input' => $request->all(),
            ]);

            return redirect()->back()
                ->withErrors($e->validator)
                ->withInput()
                ->with([
                    'success' => false,
                    'message' => 'Invalid data submitted.',
                ]);
        } catch (Exception $e) {
            Log::error('Error assigning teams to tournament', [
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'message' => $e->getMessage(),
            ]);

            return redirect()->back()->with([
                'success' => false,
                'message' => 'Error assigning teams to tournament.',
            ]);
        }
    }

    public function show($slug)
    {
        try {
            if (!Auth::user()->can($this->module . '-view')) {
                throw new Exception('Unauthorized Access');
            }

            $tournament = Tournament::with(['groups', 'matches', 'standings', 'groups.teams'])->where('slug', $slug)->first();
            if ($tournament) {
                return view('admin.pages.tournaments.show', compact('tournament'));
            }
        } catch (Exception $e) {
            Log::error("Error showing team data: ", [
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'message' => $e->getMessage()
            ]);

            return redirect()->back()->with([
                'success' => false,
                'message' => 'Error showing team data.'
            ]);
        }
    }

    public function generateFixtures(Request $request)
    {
        try {
            if (!Auth::user()->can($this->module . '-generate-fixtures')) {
                throw new Exception('Unauthorized Access');
            }

            $validated = $request->validate([
                'tournament_id' => 'required|exists:tournaments,id',
                'match_stage'   => 'required|in:group,playoffs',
            ]);

            $tournament = Tournament::with('groups.teams')->findOrFail($validated['tournament_id']);
            $stage      = $validated['match_stage'];
            $matches    = [];
            $startDate  = Carbon::parse($tournament->start_date);
            $endDate    = Carbon::parse($tournament->end_date);
            $reservedDays = 4;
            $groupStageEnd = $endDate->copy()->subDays($reservedDays);
            $period     = CarbonPeriod::create($startDate, $groupStageEnd);

            if ($stage === 'group') {
                foreach ($tournament->groups as $group) {
                    $teams = $group->teams;

                    for ($i = 0; $i < count($teams); $i++) {
                        for ($j = $i + 1; $j < count($teams); $j++) {
                            $teamA = $teams[$i];
                            $teamB = $teams[$j];
                            $dates = iterator_to_array($period);
                            $matchDate = $dates[array_rand($dates)];

                            $matches[] = [
                                'title'         => "{$teamA->name} vs {$teamB->name}",
                                'team_a_id'     => $teamA->id,
                                'team_b_id'     => $teamB->id,
                                'tournament_id' => $tournament->id,
                                'match_date'    => $matchDate->setTime(rand(9, 18), rand(0, 30)),
                                'venue'         => null,
                                'max_overs'     => $tournament->overs_per_innings,
                                'match_type'    => 'tournament',
                                'status'        => 'upcoming',
                                'stage'         => 'group',
                                'created_at'    => now(),
                                'updated_at'    => now(),
                            ];
                        }
                    }
                }
            } elseif ($stage === 'playoffs') {
                $qualifiedTeams = collect();

                foreach ($tournament->groups as $group) {
                    $topTeams = \App\Models\TournamentTeamStat::where('tournament_id', $tournament->id)
                        ->whereIn('team_id', $group->teams->pluck('id'))
                        ->orderByDesc('points')
                        ->orderByDesc('net_run_rate')
                        ->take(2)
                        ->pluck('team_id');

                    $qualifiedTeams = $qualifiedTeams->merge($topTeams);
                }

                $qualifiedTeams = $qualifiedTeams->unique()->values();

                // For simplicity: pairing 1v2, 3v4...
                for ($i = 0; $i < count($qualifiedTeams); $i += 2) {
                    if (isset($qualifiedTeams[$i + 1])) {
                        $teamAId = $qualifiedTeams[$i];
                        $teamBId = $qualifiedTeams[$i + 1];
                        $teamA = \App\Models\Team::find($teamAId);
                        $teamB = \App\Models\Team::find($teamBId);

                        $matches[] = [
                            'title' => "{$teamA->name} vs {$teamB->name}",
                            'team_a_id' => $teamA->id,
                            'team_b_id' => $teamB->id,
                            'tournament_id' => $tournament->id,
                            'match_date' => now()->addDays(rand(11, 15)), // later date
                            'venue' => null,
                            'max_overs'     => $tournament->overs_per_innings,
                            'match_type' => 'tournament',
                            'status' => 'upcoming',
                            'stage' => 'playoffs',
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    }
                }
            }

            CricketMatch::insert($matches);

            return redirect()->back()->with([
                'success' => true,
                'message' => ucfirst($stage) . ' fixtures generated successfully.',
            ]);
        } catch (ValidationException $e) {
            Log::error('Validation failed in generateFixtures', [
                'errors' => $e->validator->errors()->toArray(),
                'input'  => $request->all(),
            ]);

            return redirect()->back()
                ->withErrors($e->validator)
                ->withInput()
                ->with([
                    'success' => false,
                    'message' => 'Invalid data submitted.',
                ]);
        } catch (Exception $e) {
            Log::error('Error generating fixtures', [
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'message' => $e->getMessage(),
            ]);

            return redirect()->back()->with([
                'success' => false,
                'message' => 'Error generating fixtures.',
            ]);
        }
    }

    public function destroy($id)
    {
        try {
            $tournament = Tournament::with('groups')->findOrFail($id);

            // Delete logo if exists
            if ($tournament->logo && file_exists(storage_path('app/public/uploads/tournaments/' . $tournament->logo))) {
                unlink(storage_path('app/public/uploads/tournaments/' . $tournament->logo));
            }

            // Delete trophy image if exists
            if ($tournament->trophy_image && file_exists(storage_path('app/public/uploads/tournaments/' . $tournament->trophy_image))) {
                unlink(storage_path('app/public/uploads/tournaments/' . $tournament->trophy_image));
            }

            // Delete associated groups (and cascade deletes group_teams)
            foreach ($tournament->groups as $group) {
                $group->delete();
            }

            // Delete the tournament
            $tournament->delete();

            return redirect()->back()->with([
                'success' => true,
                'message' => 'Tournament deleted successfully.',
            ]);
        } catch (Exception $e) {
            Log::error("Error deleting tournament data", [
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'message' => $e->getMessage(),
            ]);

            return redirect()->back()->with([
                'success' => false,
                'message' => 'Error deleting tournament data.',
            ]);
        }
    }
}
