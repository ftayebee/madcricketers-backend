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
use Illuminate\Support\Facades\DB;
use App\Models\TournamentGroupTeam;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Models\TournamentPlayerStat;
use Illuminate\Support\Facades\Auth;
use Intervention\Image\Facades\Image;
use Illuminate\Http\JsonResponse;
use App\Services\TournamentFixtureService;
use Illuminate\Validation\ValidationException;

class TournamentController extends Controller
{
    protected $module = 'tournaments';

    public function bulkUpdateTeamIds($tournamentId)
    {
        try {
            DB::beginTransaction();

            $stats = TournamentPlayerStat::where('tournament_id', $tournamentId)
                ->with('player.teams')
                ->get();

            $updatedCount = 0;

            foreach ($stats as $stat) {
                $team = $stat->player->teams()->wherePivot('tournament_id', $tournamentId)->first();

                if ($team) {
                    $stat->team_id = $team->id;
                    $stat->save();
                    $updatedCount++;
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Team IDs updated for {$updatedCount} players in tournament {$tournamentId}."
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Error bulk updating team IDs:', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Something went wrong while updating team IDs.'
            ], 500);
        }
    }

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
            $tournament->group_count = ($validated['format'] === 'group') ? $validated['group_count'] : null;
            $tournament->overs_per_innings = $validated['overs_per_innings'];

            // ========== UPLOAD LOGO ==========
            if ($request->hasFile('logo')) {
                $logo = $request->file('logo');
                $logoFilename = 'tournament_logo_' . time() . '.' . $logo->getClientOriginalExtension();

                $logoPath = storage_path('app/public/uploads/tournaments');
                if (!file_exists($logoPath)) mkdir($logoPath, 0775, true);

                Image::make($logo)->save($logoPath . '/' . $logoFilename);
                $tournament->logo = $logoFilename;
            }

            // ========== UPLOAD TROPHY ==========
            if ($request->hasFile('trophy_image')) {
                $trophy = $request->file('trophy_image');
                $trophyFilename = 'trophy_' . time() . '.' . $trophy->getClientOriginalExtension();

                $trophyPath = storage_path('app/public/uploads/tournaments');
                if (!file_exists($trophyPath)) mkdir($trophyPath, 0775, true);

                Image::make($trophy)->save($trophyPath . '/' . $trophyFilename);
                $tournament->trophy_image = $trophyFilename;
            }

            $tournament->save();
            $tournament->refresh();

            // ======================================================
            // TEAM ATTACH LOGIC (DEPENDS ON FORMAT)
            // ======================================================
            if (!empty($validated['team_id'])) {

                $teams = $validated['team_id'];
                $format = $tournament->format;
                $separate = $request->has('seperate_teams');

                // -------------------------------
                // FORMAT 1: GROUP FORMAT
                // -------------------------------
                if ($format === 'group') {

                    if ($separate && !empty($tournament->group_count)) {

                        // Create groups if not created yet
                        if ($tournament->groups()->count() === 0) {
                            for ($i = 0; $i < $tournament->group_count; $i++) {
                                $tournament->groups()->create([
                                    'name' => 'Group ' . chr(65 + $i)
                                ]);
                            }
                            $tournament->load('groups');
                        }

                        shuffle($teams);
                        $groupCount = $tournament->groups->count();
                        $groups = $tournament->groups;

                        foreach ($teams as $index => $teamId) {
                            TournamentGroupTeam::create([
                                'group_id' => $groups[$index % $groupCount]->id,
                                'team_id' => $teamId,
                                'tournament_id' => $tournament->id,
                            ]);
                        }
                    } else {

                        // If no separate teams = All in Group A
                        $group = TournamentGroup::create([
                            'tournament_id' => $tournament->id,
                            'name' => 'Group A',
                        ]);

                        foreach ($teams as $teamId) {
                            TournamentGroupTeam::create([
                                'group_id' => $group->id,
                                'team_id' => $teamId,
                                'tournament_id' => $tournament->id,
                            ]);
                        }
                    }
                }

                // -------------------------------
                // FORMAT 2: ROUND ROBIN
                // -------------------------------
                elseif ($format === 'round-robin') {
                    // No groups needed
                    // Just attach all teams under a “virtual” Group A OR no group at all
                    $group = TournamentGroup::create([
                        'tournament_id' => $tournament->id,
                        'name' => 'Round Robin Group',
                    ]);

                    foreach ($teams as $teamId) {
                        TournamentGroupTeam::create([
                            'group_id' => $group->id,
                            'team_id' => $teamId,
                            'tournament_id' => $tournament->id,
                        ]);
                    }
                }

                // -------------------------------
                // FORMAT 3: KNOCKOUT
                // -------------------------------
                elseif ($format === 'knockout') {

                    // Knockout is simple — single group only
                    $group = TournamentGroup::create([
                        'tournament_id' => $tournament->id,
                        'name' => 'Knockout Bracket',
                    ]);

                    foreach ($teams as $teamId) {
                        TournamentGroupTeam::create([
                            'group_id' => $group->id,
                            'team_id' => $teamId,
                            'tournament_id' => $tournament->id,
                        ]);
                    }
                }
            }

            return redirect()->route('admin.tournaments.index')
                ->with([
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
                $stageStatus = (new TournamentFixtureService())->groupStageStatus($tournament);
                return view('admin.pages.tournaments.show', compact('tournament', 'stageStatus'));
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
                'match_stage'   => 'required|in:group,playoffs,semi-final,final',
            ]);

            $tournament = Tournament::with('groups.teams')->findOrFail($validated['tournament_id']);
            $stage      = $validated['match_stage'];

            $service = new TournamentFixtureService();
            $matches = $service->generate($tournament, $stage);

            if (empty($matches)) {
                return redirect()->back()->with([
                    'success' => false,
                    'message' => 'No fixtures could be generated. Check that teams are assigned correctly.',
                ]);
            }

            CricketMatch::insert($matches);

            return redirect()->back()->with([
                'success' => true,
                'message' => count($matches) . ' ' . ucfirst(str_replace('-', ' ', $stage)) . ' fixture(s) generated successfully.',
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
        } catch (\RuntimeException $e) {
            // Business logic errors from the service (invalid state, duplicates, etc.)
            Log::warning('Fixture generation blocked', [
                'tournament_id' => $request->input('tournament_id'),
                'stage'         => $request->input('match_stage'),
                'reason'        => $e->getMessage(),
            ]);

            return redirect()->back()->with([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        } catch (Exception $e) {
            Log::error('Error generating fixtures', [
                'line'    => $e->getLine(),
                'file'    => $e->getFile(),
                'message' => $e->getMessage(),
            ]);

            return redirect()->back()->with([
                'success' => false,
                'message' => 'An unexpected error occurred while generating fixtures.',
            ]);
        }
    }

    /**
     * Validate eligibility for a Super 8 or Super 4 next stage and,
     * if eligible, persist the admin's selection on the tournament record.
     *
     * Called via AJAX from the next-stage selection modal.
     * Returns JSON — never redirects.
     */
    public function selectNextStage(Request $request): JsonResponse
    {
        try {
            if (!Auth::user()->can($this->module . '-generate-fixtures')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized.',
                ], 403);
            }

            $validated = $request->validate([
                'tournament_id' => 'required|exists:tournaments,id',
                'next_stage'    => 'required|in:super8,super4',
            ]);

            $tournament = Tournament::with('groups.teams')->findOrFail($validated['tournament_id']);
            $nextStage  = $validated['next_stage'];

            $service     = new TournamentFixtureService();
            $eligibility = $service->getNextStageEligibility($tournament, $nextStage);

            if (!$eligibility['eligible']) {
                return response()->json([
                    'success' => false,
                    'message' => $eligibility['reason'],
                    'data'    => $eligibility,
                ]);
            }

            // Persist the selection so the generation phase can read it
            $tournament->next_stage_selection = $nextStage;
            $tournament->save();

            $label = $nextStage === 'super8' ? 'Super 8' : 'Super 4';

            return response()->json([
                'success' => true,
                'message' => "{$label} stage confirmed. You can now generate fixtures for this stage.",
                'data'    => array_merge($eligibility, [
                    'next_stage' => $nextStage,
                    'label'      => $label,
                ]),
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid input.',
                'errors'  => $e->errors(),
            ], 422);
        } catch (Exception $e) {
            Log::error('Error in selectNextStage', [
                'message' => $e->getMessage(),
                'line'    => $e->getLine(),
                'file'    => $e->getFile(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred.',
            ], 500);
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
